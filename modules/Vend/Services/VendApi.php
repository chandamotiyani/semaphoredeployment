<?php


namespace modules\Vend\Services;

use \Craft;
use craft\base\Component;
use craft\elements\User;
use craft\helpers\DateTimeHelper;
use GuzzleHttp\Client;
use modules\Memberships\MembershipModule;
use modules\Vend\Errors\VendException;
use modules\Vend\Responses\CreateCustomerResponse;
use modules\Vend\Responses\DeleteCustomerResponse;
use modules\Vend\Responses\Traits\HasLog;
use modules\Vend\Responses\UpdateCustomerResponse;

class VendApi extends Component {
    use HasLog;

    private $url = 'vendhq.com/api/2.0';
    private $protocol = 'https://';
    private $http;
    private $config;

    public function __construct( Client $http ) {
        $this->config = \Craft::$app->getConfig()->getConfigFromFile('services')['vend-api'];
        $this->http = $http;
    }

    /**
     * @param array $users
     *
     * @throws VendException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function sendMembers(array $users){
        foreach($users as $user){
            $this->sendMember($user);
        }
    }

    /**
     * @param User $user
     *
     * @return bool
     * @throws VendException
     */
    public function deleteMember(User $user){
        //TODO: implement delete member methods.

        if(isset($user->vendCustomerId) && $user->vendCustomerId){
            //existing user.
            $body = $this->makeBody($user, 'deleted');
            $url = $this->makeUrl("customers/".$user->vendCustomerId);
            $options = $this->makeOptions($body);

            $this->log("delete vend customer (ie move to group)", json_encode($body), $user->id);
            $response = new DeleteCustomerResponse($this->http, $url, $options);
            $this->log("response delete vend customer (ie move to group)", json_encode([]), $user->id);
            if($response->isSuccessful()) {
                return true;
            }
            else{
                throw new VendException('Could not move vend member to deleted group - Response code '.$response->getResponseCode() . ' received: '.$response->response->getBody());
                return false;
            }
        }
    }

    /**
     * @param User $user
     *
     * @return User
     * @throws VendException
     * @throws \yii\db\Exception
     */
    public function sendMember(User $user){
        $groupIds = MembershipModule::getInstance()->members->getUserGroupIds($user);
        $membership = MembershipModule::getInstance()->members->getYalumbaMemberUserGroup($groupIds);
        if(isset($user->vendCustomerId) && $user->vendCustomerId){
            //existing user.
            $body = $this->makeBody($user, $membership);
            $url = $this->makeUrl("customers/".$user->vendCustomerId);
            $options = $this->makeOptions($body);
            $this->log("update vend customer", json_encode($body), $user->id);
            $response = new UpdateCustomerResponse($this->http, $url, $options);
            $this->log("response: update vend customer", json_encode($response->getData()), $user->id);
            if(!$response->isSuccessful()){
                throw new VendException('Vend Customer update failed - Response code '.$response->getResponseCode() . ' received: '.$response->response->getBody());
            }
        }
        else{
            //create new user
            $body = $this->makeBody($user, $membership);
            $url = $this->makeUrl("customers");
            $options = $this->makeOptions($body);
            $this->log("create vend customer", json_encode($body), $user->id);
            $response = new CreateCustomerResponse($this->http, $url, $options );
            $this->log("response: create vend customer", json_encode($response->getData()), $user->id);
            if($response->customerExists()){
                $body = $this->makeBody($user, $membership);
                $url = $this->makeUrl("customers/".$user->vendCustomerId);
                $options = $this->makeOptions($body);
                $this->log("Member already created. update vend customer", json_encode($body), $user->id);
                $response = new UpdateCustomerResponse($this->http, $url, $options);
                $this->log("response: Member already created. update vend customer", json_encode($body), $user->id);
            }
            if($response->isSuccessful()){
                //TODO: should send member be doing this? - we should move this into separate area.
                // save the user with the vend id back to the system in a custom field.
                // WE CAN'T USE ELEMENT->SAVE HERE BECAUSE IT WILL TRIGGER ANOTHER EVENT.
                // SO WE HAVE TO RUN THIS QUERY MANUALLY UNLESS THERE's SOME WAY OF DISABLING
                // THE EVENT NICELY?
                $user->vendCustomerId = (string) $response->getData()->id;
                $db = Craft::$app->getDb();
                $params = [
                    'userId'=>$user->id,
                    'vendCustomerId'=>$user->vendCustomerId
                ];
                $sql = "UPDATE {{%content}} SET field_vendCustomerId = :vendCustomerId WHERE elementId = :userId";
                $db->createCommand($sql, $params)->execute();
                return $user;

            }
            else{
                throw new VendException('Vend Create Customer failed - Response code '.$response->getResponseCode() . ' received: '.$response->response->getBody());
                \Craft::getLogger()->log($body, \yii\log\Logger::LEVEL_TRACE, "vend_api");
            }
        }
        return $user;
    }

    /**
     * @param User $user
     * @param $membership
     *
     * @return array
     */
    private function makeBody(User $user, $membership){
        if($membership == 'deleted'){
            $customerGroupId = $this->getDeletedGroupId();
        }
        else if($membership){
            $customerGroupId = $this->getCustomerGroupIdFromMembership($membership);
        }
        else{
            $customerGroupId = false;
        }
        if ($user->dateOfBirth instanceof \DateTime){
            $dateOfBirth = $user->dateOfBirth->format('Y-m-d');
        }
        else{
            $dateOfBirth = $user->dateOfBirth;
        }
        $body=[
            'first_name'=>$user->firstName,
            'last_name'=>$user->lastName,
            'email'=>$user->email,
            "phone" => $user->phoneNumber,
            /* Chanda - customer_code will contain the Hub Id if it exist */
            'customer_code'=> $user->yalumbaHubId ? $user->yalumbaHubId : $user->uid,
            /* End */
            "date_of_birth" => $dateOfBirth,
            'custom_field_1'=>'craft'
        ];
        if($customerGroupId){
            $body['customer_group_id'] = $customerGroupId?$customerGroupId:'';
        }

        if($user->vendCustomerId){
            $body['id'] = $user->vendCustomerId;
        }

        return $body;
    }

    /**
     * creates header for sending vend data
     * @param null $body
     *
     * @return array
     */
    private function makeOptions($body = NULL){

        $options = [
            "headers"=>[
                "Authorization"=>"Bearer ".$this->config['personal-token'],
                "Content-Type"=>"application/json",
            ],
            //'debug' => true

        ];
        if($body){
            $options["body"] = json_encode($body, true);
        }
        return $options;

    }

    /**
     * get the deleted customer group for use with deletion
     * @return mixed
     */
    public function getDeletedGroupId(){
        return $this->config['customer-groups']['deleted'];
    }

    /**
     * @param $membership
     *
     * @return bool|mixed
     */
    public function getCustomerGroupIdFromMembership($membership){
        if(isset($this->config['customer-groups'][$membership->handle])){
            return $this->config['customer-groups'][$membership->handle];
        }
        return false;
    }

    /**
     * make me a url
     * @param string $url
     *
     * @return string
     */
    private function makeUrl(string $url){
        return "$this->protocol".$this->config['domain-prefix']."."."$this->url/$url";
    }
}
