import { Controller } from '@hotwired/stimulus';
import Calendar from '@toast-ui/calendar';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
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
    }

    updateCurrentDateFromDateInput() {
        this.currentDate = new Date(this.dateInputTarget.value);
        this.calendar.setDate(this.currentDate);
        this.updateStringDate();
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
        });
    }
}


// calendar.createEvents([
//     {
//         id: '1',
//         calendarId: 'cal1',
//         title: 'timed event',
//         body: 'TOAST UI Calendar',
//         start: '2024-07-25',
//         end: '2024-07-25',
//         location: 'Meeting Room A',
//         attendees: ['A', 'B', 'C'],
//         category: 'allday',
//         state: 'Free',
//         isReadOnly: false,
//     }, // EventObject
// ]);
