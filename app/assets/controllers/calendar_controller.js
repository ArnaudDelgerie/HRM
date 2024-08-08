import { Controller } from '@hotwired/stimulus';
import Calendar from '@toast-ui/calendar';
import { getSassVariable } from '../app';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = { 'alldayEventsUrl': String, 'meetingEventsUrl': String };
    static targets = ['dateInput', 'viewInput', 'stringDate', 'calendar', 'navCta'];

    initialize() {
        this.view = 'week';
        this.calendar = null;
        this.currentDate = new Date();
    }

    connect() {
        this.initCalendar();
        // this.updateView();
        if (this.dateInputTarget.value) {
            this.updateCurrentDateFromDateInput();
        } else {
            this.updateDateInputFromCurrentDate();
        }
    }

    updateDateInputFromCurrentDate() {
        this.dateInputTarget.value = this.currentDate.toISOString().split('T')[0];
        this.calendar.setDate(this.currentDate);
        this.updateStringDate();
        this.getAlldayEvents();
        this.getMeetingEvents();
    }

    updateCurrentDateFromDateInput() {
        this.currentDate = new Date(this.dateInputTarget.value);
        this.calendar.setDate(this.currentDate);
        this.updateStringDate();
        this.getAlldayEvents();
        this.getMeetingEvents();
    }

    updateStringDate() {
        const options = { month: 'long', year: 'numeric' };
        const stringDate = this.currentDate.toLocaleString('fr-FR', options);
        this.stringDateTarget.innerText = stringDate;
    }

    updateView() {
        this.view = this.viewInputTarget.value;
        this.calendar.changeView(this.view);
        this.navCtaTargets.forEach((navCta) => {
            if (this.view === navCta.getAttribute('data-calendar-nav-cta-type')) {
                navCta.classList.remove('d-none');
            } else {
                navCta.classList.add('d-none');
            }
        })
    }

    removeDay({ params: { nbDay } }) {
        this.currentDate.setDate(this.currentDate.getDate() - nbDay);
        this.updateDateInputFromCurrentDate();
    }

    addDay({ params: { nbDay } }) {
        this.currentDate.setDate(this.currentDate.getDate() + nbDay);
        this.updateDateInputFromCurrentDate();
    }

    removeMonth({ params: { nbMonth } }) {
        this.currentDate.setMonth(this.currentDate.getMonth() - nbMonth);
        this.updateDateInputFromCurrentDate();
    }

    addMonth({ params: { nbMonth } }) {
        this.currentDate.setMonth(this.currentDate.getMonth() + nbMonth);
        this.updateDateInputFromCurrentDate();
    }

    initCalendar() {
        this.calendar = new Calendar(this.calendarTarget, {
            defaultView: this.view,
            isReadOnly: false,
            week: {
                workweek: true,
                hourStart: 9,
                hourEnd: 18,
                taskView: false,
                startDayOfWeek: 1,
                dayNames: ['dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam'],
            },
            month: {
                workweek: true,
                startDayOfWeek: 1,
                dayNames: ['dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam'],
                isAlways6Weeks: false,
            },
            template: {
                alldayTitle() {
                    return `<div>Journée</div>`;
                },
                timegridDisplayPrimaryTime({ time }) {
                    return `${time.getHours()}h`;
                },
            },
            theme: {
                common: {
                    backgroundColor: getSassVariable('light'),
                }
            }
        });
        this.calendar.on('clickEvent', ({ event }) => {
            window.open(event.id, '_blank');
        });
    }

    getAlldayEvents() {
        fetch(`${this['alldayEventsUrlValue']}?date=${encodeURIComponent(this.currentDate.toISOString())}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'include',
        })
            .then(response => response.json())
            .then(data => {
                data.dayLeaveRequests.forEach((dlr) => {
                    dlr.backgroundColor = getSassVariable('info')
                    if (!this.calendar.getEvent(dlr.id, dlr.calendarId)) {
                        this.calendar.createEvents([dlr])
                    } else {
                        this.calendar.updateEvent(dlr.id, dlr.calendarId, dlr);
                    }
                })
                data.holidays.forEach((holiday) => {
                    holiday.backgroundColor = getSassVariable('danger')
                    if (!this.calendar.getEvent(holiday.id, holiday.calendarId)) {
                        this.calendar.createEvents([holiday])
                    } else {
                        this.calendar.updateEvent(holiday.id, holiday.calendarId, holiday);
                    }
                })
            })
            .catch(error => console.error(error));
    }

    getMeetingEvents() {
        fetch(`${this['meetingEventsUrlValue']}?date=${encodeURIComponent(this.currentDate.toISOString())}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'include',
        })
            .then(response => response.json())
            .then(data => {
                data.meetings.forEach((meeting) => {
                    meeting.backgroundColor = getSassVariable('secondary')
                    if (!this.calendar.getEvent(meeting.id, meeting.calendarId)) {
                        this.calendar.createEvents([meeting])
                    } else {
                        this.calendar.updateEvent(meeting.id, meeting.calendarId, meeting);
                    }
                })
            })
            .catch(error => console.error(error));
    }
}
