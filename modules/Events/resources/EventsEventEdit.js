/** global: Craft */
/** global: Garnish */
/**
 * Event index class
 */

Craft.Events.EventEdit = Craft.BaseElementEditor.extend({

    $container:null,
    elementType:null,

    $addScheduleButtons: null,
    $filterButtons: null,
    $blankScheduleControls: null,
    $deleteScheduleList: null,

    $apiIdControl:null,
    $apiScheduleContainer: null,
    $manualScheduleContainer: null,

    itemsToDelete: [],
    newCount: 0,

    init: function (elementType, $container, settings) {
        this.$container = $('#main-container'); //TODO: revisit this - this is real gross.

        this.$addScheduleButtons =  this.$container.find(`[data-action='add-schedule']`);
        this.$deleteScheduleButtons =  this.$container.find(`[data-action='delete-schedule']`);
        this.$blankScheduleControls =  this.$container.find(`[data-key='blank-schedule-controls']`);

        this.$deleteScheduleList = this.$container.find(`[data-key='delete-schedule-control']`);
        this.$filterButtons = this.$container.find(`.event-filter`);
        this.$apiIdControl = this.$container.find(`#apiId-dropdown`);
        this.$apiScheduleContainer = this.$container.find(`.schedule-api`);
        this.$manualScheduleContainer = this.$container.find(`.schedule-manual`);

        this.addListener(this.$apiIdControl, 'change', 'changeApiId');
        this.addListener(this.$addScheduleButtons, 'click', 'addSchedule');
        this.addListener(this.$deleteScheduleButtons, 'click', 'deleteSchedule');
        this.addListener(this.$filterButtons, 'click', 'filterSchedules');
        //super.init(elementType, $container, settings);
        if(this.$apiIdControl[0].value){
            this.$manualScheduleContainer.find('input').prop('disabled', true);
            this.$manualScheduleContainer.hide();
            this.$apiScheduleContainer.show();
        }
        else{
            this.$manualScheduleContainer.find('input').prop('disabled', false);
            this.$manualScheduleContainer.show();
            this.$apiScheduleContainer.hide();
        }
    },

    filterSchedules: function(e){
        let $target = $(e.target);
        this.$filterButtons.removeClass('active');
        $target.addClass('active');
        if($target.data('filter') == 'show-all'){
            $('.data-row').show();
        }
        else{
            $('.data-row').hide();
            $('.data-row.'+$target.data('filter')).show();
        }
    },

    deleteSchedule: function(e){
        let $target = $(e.target);
        let $element = $target.closest(`[data-item='`+$target.data('item-to-delete')+`']`);
        this.itemsToDelete.push($target.data('item-id'));
        this.updateToDelete();
        $element.remove();
    },
    updateToDelete: function(){
        this.$deleteScheduleList.val(this.itemsToDelete);
    },
    addSchedule: function () {
        let $c = this.$blankScheduleControls.clone(true);
        //we need to change the var name(s) and remove the ids so datepicker doesn't get confused.
        $c.find(`[name='fields[startDateTime][date]']`).removeAttr('id').attr('name', `newSchedule[`+this.newCount+`][startDateTime][date]`);
        $c.find(`[name='fields[startDateTime][time]']`).removeAttr('id').attr('name', `newSchedule[`+this.newCount+`][startDateTime][time]`);
        $c.find(`[name='fields[startDateTime][timezone]']`).removeAttr('id').attr('name', `newSchedule[`+this.newCount+`][startDateTime][timezone]`);
        $c.find(`[name='fields[endDateTime][date]']`).removeAttr('id').attr('name', `newSchedule[`+this.newCount+`][endDateTime][date]`);
        $c.find(`[name='fields[endDateTime][time]']`).removeAttr('id').attr('name', `newSchedule[`+this.newCount+`][endDateTime][time]`);
        $c.find(`[name='fields[endDateTime][timezone]']`).removeAttr('id').attr('name', `newSchedule[`+this.newCount+`][endDateTime][timezone]`);
        $c.find(`[name='fields[ticketsAvailable]']`).removeAttr('id').attr('name', `newSchedule[`+this.newCount+`][ticketsAvailable]`);
        $c.find(`[name='fields[scheduleGroupId]']`).removeAttr('id').attr('name', `newSchedule[`+this.newCount+`][groupId]`);
        //insert the clone into 1st position
        $c.removeClass().insertAfter(this.$blankScheduleControls).addClass('newSchedule').attr('data-item','newSchedule['+this.newCount+']');
        //and re-init the datepicker
        $c.find('.hasDatepicker').removeClass('hasDatepicker').removeData('datepicker').unbind().datepicker();

        //and attach the delete button data (so we know what to delete);
        $c.find(`[data-action='delete-schedule']`).attr('data-item-to-delete', 'newSchedule['+this.newCount+']');
        this.newCount ++;
    },
    changeApiId: function(e){
        if(e.currentTarget.value){
            this.$manualScheduleContainer.find('input').prop('disabled', true);
            this.$manualScheduleContainer.hide();
            this.$apiScheduleContainer.show();
        }
        else{
            this.$manualScheduleContainer.find('input').prop('disabled', false);
            this.$manualScheduleContainer.show();
            this.$apiScheduleContainer.hide();
        }
    }

});

Craft.registerElementEditorClass('modules\\Events\\Elements\\Event', Craft.Events.EventEdit);