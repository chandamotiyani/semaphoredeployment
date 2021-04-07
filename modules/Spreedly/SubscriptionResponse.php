<?php
namespace modules\Spreedly;

use craft\commerce\base\SubscriptionResponseInterface;
use craft\helpers\StringHelper;
use DateTime;
use DateInterval;

class SubscriptionResponse implements SubscriptionResponseInterface
{
	/**
	 * @var bool Whether this subscription is canceled
	 */
	private $_isCanceled = false;

	/**
	 * @var int Amount of trial days
	 */
	private $_trialDays = 0;

	/**
	 * @var int the id of the payment source to use. !!! TODO: SECURITY - THIS MUST BE CHECKED AND CROSS CHECKED THAT IT BELONGS TO THE USER!!!!!!
	 */
	private $_data =[];

	private $_reference;

	/**
	 * @inheritdoc
	 */
	public function setIsCanceled(bool $isCanceled)
	{
		$this->_isCanceled = $isCanceled;
	}


	/**
	 * @inheritdoc
	 */
	public function setTrialDays(int $trialDays)
	{
		$this->_trialDays = $trialDays;
	}

	public function setData($data) {
		$this->_data = $data;
	}

	/**
	 * @inheritdoc
	 */
	public function getData()
	{
		return $this->_data;
	}


	/**
	 * @inheritdoc
	 */
	public function getTrialDays(): int
	{
		return $this->_trialDays;
	}

	/**
	 * @inheritdoc
	 */
	public function getNextPaymentDate(): DateTime
	{
		return (new DateTime())->add(new DateInterval('P1Y'));
	}

	/**
	 * @inheritdoc
	 */
	public function isCanceled(): bool
	{
		return $this->_isCanceled;
	}

	/**
	 * @inheritdoc
	 */
	public function isScheduledForCancellation(): bool
	{
		return $this->_isCanceled;
	}

	/**
	 * @inheritdoc
	 */
	public function isInactive(): bool
	{
		return false;
	}

	/**
	 * Returns the subscription reference.
	 *
	 * @return string
	 */
	public function getReference(): string {
		return $this->_reference;
	}


	/**
	 * Returns the subscription reference.
	 *
	 * @return string
	 */
	public function setReference($reference): string {
		$this->_reference = $reference;
		return $this->_reference;
	}

	public function getSubscriptionData(){
		return [];
	}
}