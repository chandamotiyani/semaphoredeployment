/** global: Craft */
/** global: Garnish */
/**
 * Event index class
 */

Craft.Events.EventIndex = Craft.BaseElementIndex.extend({

    editableEventTypes: null,
    $newEventBtnEventType: null,
    $newEventBtn: null,

    init: function (elementType, $container, settings) {
        this.on('selectSource', $.proxy(this, 'updateButton'));
        this.on('selectSite', $.proxy(this, 'updateButton'));
        this.base(elementType, $container, settings);
    },
    afterInit: function() {
        // Find which of the visible eventTypes the user has permission to create new events in
        this.editableEventTypes = [];
        for (var i = 0; i < Craft.Events.editableEventTypes.length; i++) {
            var eventType = Craft.Events.editableEventTypes[i];

            if (this.getSourceByKey('eventgroup:' + eventType.id)) {
                this.editableEventTypes.push(eventType);
            }
        }

        this.base();
    },
    updateButton: function () {
        // Update the New Event button
        // ---------------------------------------------------------------------

        if (!this.$source) {
            return;
        }

        // Get the handle of the selected source
        var selectedSourceHandle = this.$source.data('handle');

        var i, href, label;

        // Update the New Event button
        // ---------------------------------------------------------------------
        if (this.editableEventTypes.length) {

            // Remove the old button, if there is one
            if (this.$newEventBtnEventType) {
                this.$newEventBtnEventType.remove();
            }

            // Determine if they are viewing a eventType that they have permission to create events in
            var selectedEventType;

            if (selectedSourceHandle) {
                for (i = 0; i < this.editableEventTypes.length; i++) {
                    if (this.editableEventTypes[i].handle === selectedSourceHandle) {
                        selectedEventType = this.editableEventTypes[i];
                        break;
                    }
                }
            }

            this.$newEventBtnEventType = $('<div class="btngroup submit"/>');
            var $menuBtn;

            // If they are, show a primary "New events" button, and a dropdown of the other eventTypes (if any).
            // Otherwise only show a menu button
            if (selectedEventType) {
                href = this._getEventTypeTriggerHref(selectedEventType);
                label = (this.settings.context === 'index' ? Craft.t('app', 'New event') : Craft.t('app', 'New {eventType} event', {eventType: selectedEventType.name}));
                this.$newEventBtn = $('<a class="btn submit add icon" ' + href + '>' + Craft.escapeHtml(label) + '</a>').appendTo(this.$newEventBtnEventType);

                if (this.settings.context !== 'index') {
                    this.addListener(this.$newEventBtn, 'click', function(ev) {
                        this._openCreateEventModal(ev.currentTarget.getAttribute('data-id'));
                    });
                }

                if (this.editableEventTypes.length > 1) {
                    $menuBtn = $('<div class="btn submit menubtn"></div>').appendTo(this.$newEventBtnEventType);
                }
            }
            else {
                this.$newEventtBtn = $menuBtn = $('<div class="btn submit add icon menubtn">' + Craft.t('app', 'New event') + '</div>').appendTo(this.$newEventBtnEventType);
            }

            if ($menuBtn) {
                var menuHtml = '<div class="menu"><ul>';

                for (i = 0; i < this.editableEventTypes.length; i++) {
                    var eventType = this.editableEventTypes[i];

                    if (this.settings.context === 'index' || eventType !== selectedEventType) {
                        href = this._getEventTypeTriggerHref(eventType);
                        label = (this.settings.context === 'index' ? eventType.name : Craft.t('app', 'New {eventType} event', {eventType: eventType.name}));
                        menuHtml += '<li><a ' + href + '">' + Craft.escapeHtml(label) + '</a></li>';
                    }
                }

                menuHtml += '</ul></div>';

                $(menuHtml).appendTo(this.$newEventBtnEventType);
                var menuBtn = new Garnish.MenuBtn($menuBtn);

                if (this.settings.context !== 'index') {
                    menuBtn.on('optionSelect', $.proxy(function(ev) {
                        this._openCreateEventModal(ev.option.getAttribute('data-id'));
                    }, this));
                }
            }

            this.addButton(this.$newEventBtnEventType);
        }

        // Update the URL if we're on the Categories index
        // ---------------------------------------------------------------------

        if (this.settings.context === 'index' && typeof history !== 'undefined') {
            var uri = 'events';

            if (selectedSourceHandle) {
                uri += '/' + selectedSourceHandle;
            }

            history.replaceState({}, '', Craft.getUrl(uri));
        }
    },

    _getEventTypeTriggerHref: function(eventType) {
        if (this.settings.context === 'index') {
            var uri = 'events/' + eventType.handle + '/new';
            if (this.siteId && this.siteId != Craft.primarySiteId) {
                for (var i = 0; i < Craft.sites.length; i++) {
                    if (Craft.sites[i].id == this.siteId) {
                        uri += '/'+Craft.sites[i].handle;
                    }
                }
            }
            return 'href="' + Craft.getUrl(uri) + '"';
        }
        else {
            return 'data-id="' + eventType.id + '"';
        }
    },
});

Craft.registerElementIndexClass('modules\\Events\\Elements\\Event', Craft.Events.EventIndex);