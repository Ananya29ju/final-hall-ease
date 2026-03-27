@extends('layouts/contentNavbarLayout')

@section('title', 'Staff Dashboard')

@section('page-style')
<style>
    .user-dashboard-scale {
        font-size: 1.06rem;
    }

    .admin-calendar-card .calendar-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .admin-calendar-card .calendar-month-label {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
        color: #2f3349;
    }

    .admin-calendar-card .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 8px;
    }

    .admin-calendar-card .calendar-weekday {
        text-align: center;
        font-size: 0.78rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #697a8d;
        font-weight: 700;
        padding: 8px 4px;
        border-bottom: 1px solid #e9ecef;
    }

    .admin-calendar-card .calendar-day {
        min-height: 96px;
        border: 1px solid #eceff4;
        border-radius: 10px;
        background: #fff;
        padding: 8px 7px;
        display: flex;
        flex-direction: column;
        font-weight: 500;
        color: #566a7f;
        transition: all 0.2s ease;
        font-size: 0.9rem;
        cursor: pointer;
    }

    .admin-calendar-card .calendar-day-number {
        text-align: right;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .admin-calendar-card .calendar-day.has-bookings {
        background: #eef5ff;
        border-color: #c9defe;
    }

    .admin-calendar-card .calendar-events {
        display: flex;
        flex-direction: column;
        gap: 3px;
        overflow-y: auto;
        max-height: 92px;
        padding-right: 2px;
    }

    .admin-calendar-card .calendar-event {
        font-size: 0.65rem;
        line-height: 1.25;
        background: #0d6efd;
        color: #fff;
        padding: 2px 4px;
        border-radius: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .admin-calendar-card .calendar-day.is-other-month {
        background: #f8f9fb;
        color: #a8b1bd;
    }

    .admin-calendar-card .calendar-day.is-today {
        border-color: #0d6efd;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.14);
        color: #0d6efd;
    }

    .admin-calendar-card .calendar-day.is-weekend {
        background: #fbfcff;
    }

    .admin-calendar-card .calendar-detail-item {
        border: 1px solid #e8edf5;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 8px;
        background: #f9fbff;
    }

    .admin-calendar-card .calendar-detail-table-wrap {
        max-height: 55vh;
        overflow-y: auto;
    }

    .admin-calendar-card .calendar-detail-table {
        font-size: 0.88rem;
        margin-bottom: 0;
    }

    .admin-calendar-card .calendar-detail-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f4f7fc;
        color: #4d5b70;
        font-weight: 700;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .admin-calendar-card .calendar-day {
            min-height: 52px;
            font-size: 0.82rem;
            padding: 6px 5px;
        }

        .admin-calendar-card .calendar-month-label {
            font-size: 1rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y user-dashboard-scale">
    <!-- Quick Actions & Helpful Links -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">
                        <span class="badge" style="background: white; color: #ffc107; font-size: 2.0rem; display: inline-block; margin-right: 8px;">⚡</span><span style="font-size: 2.0rem; font-weight: 700;">Quick Actions</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('user.bookings.create') }}" class="list-group-item list-group-item-action px-4 py-3 d-flex align-items-center border-0">
                            <span style="background: white; padding: 8px 10px; border-radius: 6px; display: flex; align-items: center; justify-content: center;"><i class="bx bx-plus-circle text-primary" style="font-size: 1.3rem;"></i></span>
                            <div>
                                <div class="fw-bold">Book a Hall</div>
                                <small class="text-muted">Create a new booking request</small>
                            </div>
                        </a>
                        <a href="{{ route('user.halls.index') }}" class="list-group-item list-group-item-action px-4 py-3 d-flex align-items-center border-0">
                            <span style="background: white; padding: 8px 10px; border-radius: 6px; display: flex; align-items: center; justify-content: center;"><i class="bx bx-search-alt text-success" style="font-size: 1.3rem;"></i></span>
                            <div>
                                <div class="fw-bold">Explore Halls</div>
                                <small class="text-muted">View available halls and details</small>
                            </div>
                        </a>
                        <a href="{{ route('user.bookings.index') }}" class="list-group-item list-group-item-action px-4 py-3 d-flex align-items-center border-0">
                            <span style="background: white; padding: 8px 10px; border-radius: 6px; display: flex; align-items: center; justify-content: center;"><i class="bx bx-list-ul text-info" style="font-size: 1.3rem;"></i></span>
                            <div>
                                <div class="fw-bold">View My Bookings</div>
                                <small class="text-muted">Check your booking history</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">
                        <span class="badge" style="background: white; color: #0dcaf0; font-size: 2.0rem; display: inline-block; margin-right: 8px;">⚙️</span><span style="font-size: 2.0rem; font-weight: 700;">Helpful Links</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('user.halls.index') }}" class="list-group-item list-group-item-action px-4 py-3 d-flex align-items-center justify-content-between border-0">
                            <div class="d-flex align-items-center">
                                <span style="background: white; padding: 8px 10px; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 12px;"><i class="bx bx-building text-primary" style="font-size: 1.3rem;"></i></span>
                                <div>
                                    <div class="fw-bold">Hall Directory</div>
                                    <small class="text-muted">Find the right hall for your event</small>
                                </div>
                            </div>
                            <i class="bx bx-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('user.bookings.index') }}" class="list-group-item list-group-item-action px-4 py-3 d-flex align-items-center justify-content-between border-0">
                            <div class="d-flex align-items-center">
                                <span style="background: white; padding: 8px 10px; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 12px;"><i class="bx bx-calendar text-success" style="font-size: 1.3rem;"></i></span>
                                <div>
                                    <div class="fw-bold">Booking History</div>
                                    <small class="text-muted">Review your past and upcoming events</small>
                                </div>
                            </div>
                            <i class="bx bx-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('user-logout-form').submit();" class="list-group-item list-group-item-action px-4 py-3 d-flex align-items-center justify-content-between border-0">
                            <div class="d-flex align-items-center">
                                <span style="background: white; padding: 8px 10px; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 12px;"><i class="bx bx-log-out text-danger" style="font-size: 1.3rem;"></i></span>
                                <div>
                                    <div class="fw-bold">Logout</div>
                                    <small class="text-muted">Sign out of your account safely</small>
                                </div>
                            </div>
                            <i class="bx bx-chevron-right text-muted"></i>
                        </a>
                        <form id="user-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Calendar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card admin-calendar-card">
                <div class="card-body p-3 p-md-4">
                    <h4 class="card-title mb-3 d-flex align-items-center justify-content-center gap-2 text-center fw-bold">
                        <i class="bx bx-party text-primary" style="font-size: 1.6rem;"></i>
                        <span>Event Calendar</span>
                    </h4>
                    <div class="calendar-toolbar mb-3">
                        <h4 class="calendar-month-label" id="user-calendar-month">Month Year</h4>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" id="user-calendar-prev">
                                <i class="bx bx-chevron-left"></i> Prev
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2" id="user-calendar-today">
                                Today
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" id="user-calendar-next">
                                Next <i class="bx bx-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="calendar-grid mb-2" id="user-calendar-weekdays">
                        <div class="calendar-weekday">Sun</div>
                        <div class="calendar-weekday">Mon</div>
                        <div class="calendar-weekday">Tue</div>
                        <div class="calendar-weekday">Wed</div>
                        <div class="calendar-weekday">Thu</div>
                        <div class="calendar-weekday">Fri</div>
                        <div class="calendar-weekday">Sat</div>
                    </div>

                    <div class="calendar-grid" id="user-calendar-days"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class="bx bx-info-circle me-2" style="font-size: 1.3rem;"></i>
                <div>
                    <strong>Tip:</strong> Use the left menu to navigate quickly and manage your bookings with ease.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="userCalendarDayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userCalendarDayModalTitle">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userCalendarDayModalBody"></div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarBookings = @json($calendar_bookings ?? []);
        const monthLabel = document.getElementById('user-calendar-month');
        const daysContainer = document.getElementById('user-calendar-days');
        const prevButton = document.getElementById('user-calendar-prev');
        const nextButton = document.getElementById('user-calendar-next');
        const todayButton = document.getElementById('user-calendar-today');
        const dayModalElement = document.getElementById('userCalendarDayModal');
        const dayModalTitle = document.getElementById('userCalendarDayModalTitle');
        const dayModalBody = document.getElementById('userCalendarDayModalBody');
        const dayModal = dayModalElement ? new bootstrap.Modal(dayModalElement) : null;

        if (!monthLabel || !daysContainer || !prevButton || !nextButton || !todayButton) return;

        const now = new Date();
        let visibleYear = now.getFullYear();
        let visibleMonth = now.getMonth();
        const formatter = new Intl.DateTimeFormat('en-US', { month: 'long' });
        const bookingsByDate = calendarBookings.reduce((acc, booking) => {
            if (!booking.date) return acc;
            if (!acc[booking.date]) acc[booking.date] = [];
            acc[booking.date].push(booking);
            return acc;
        }, {});

        function toDateKey(year, month, day) {
            const m = String(month + 1).padStart(2, '0');
            const d = String(day).padStart(2, '0');
            return `${year}-${m}-${d}`;
        }

        function openDayModal(dayKey, dayBookings) {
            if (!dayModal || !dayModalTitle || !dayModalBody) return;

            dayModalTitle.textContent = 'Booking Time Details';
            dayModalBody.innerHTML = '';

            if (dayBookings.length === 0) {
                dayModalBody.innerHTML = '<p class="text-muted mb-0">No bookings available.</p>';
                dayModal.show();
                return;
            }

            const sortedBookings = [...dayBookings].sort((a, b) =>
                (a.start_time || '').localeCompare(b.start_time || '')
            );
            const hallCount = new Set(sortedBookings.map(b => b.hall_name)).size;

            const summary = document.createElement('div');
            summary.className = 'd-flex flex-wrap gap-2 mb-3';
            summary.innerHTML = `
                <span class="badge bg-primary">Total Events: ${sortedBookings.length}</span>
                <span class="badge bg-info text-dark">Halls: ${hallCount}</span>
            `;
            dayModalBody.appendChild(summary);

            const tableWrap = document.createElement('div');
            tableWrap.className = 'calendar-detail-table-wrap';
            const table = document.createElement('table');
            table.className = 'table table-sm table-hover calendar-detail-table align-middle';

            const thead = document.createElement('thead');
            thead.innerHTML = `
                <tr>
                    <th>Time</th>
                    <th>Hall</th>
                    <th>Staff</th>
                    <th>Event</th>
                </tr>
            `;
            table.appendChild(thead);

            const tbody = document.createElement('tbody');

            sortedBookings.forEach(function (booking) {
                const row = document.createElement('tr');

                const timeCell = document.createElement('td');
                timeCell.textContent = `${booking.start_time}-${booking.end_time}`;
                timeCell.className = 'fw-semibold text-primary';

                const hallCell = document.createElement('td');
                hallCell.textContent = booking.hall_name;

                const eventCell = document.createElement('td');
                eventCell.textContent = booking.event_name;

                const staffCell = document.createElement('td');
                staffCell.textContent = booking.booked_by;

                row.appendChild(timeCell);
                row.appendChild(hallCell);
                row.appendChild(staffCell);
                row.appendChild(eventCell);
                tbody.appendChild(row);
            });
            table.appendChild(tbody);
            tableWrap.appendChild(table);
            dayModalBody.appendChild(tableWrap);

            dayModal.show();
        }

        function renderCalendar(year, month) {
            monthLabel.textContent = formatter.format(new Date(year, month, 1));
            daysContainer.innerHTML = '';
            const fragment = document.createDocumentFragment();

            const firstDay = new Date(year, month, 1).getDay();
            const daysInCurrentMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();

            for (let i = firstDay - 1; i >= 0; i--) {
                const day = daysInPrevMonth - i;
                const cell = document.createElement('div');
                cell.className = 'calendar-day is-other-month';
                cell.textContent = day;
                fragment.appendChild(cell);
            }

            for (let day = 1; day <= daysInCurrentMonth; day++) {
                const weekday = new Date(year, month, day).getDay();
                const dayKey = toDateKey(year, month, day);
                const dayBookings = bookingsByDate[dayKey] || [];
                const cell = document.createElement('div');
                cell.className = 'calendar-day';
                const dayNumber = document.createElement('div');
                dayNumber.className = 'calendar-day-number';
                dayNumber.textContent = day;
                cell.appendChild(dayNumber);

                if (dayBookings.length > 0) {
                    cell.classList.add('has-bookings');
                    const eventsWrap = document.createElement('div');
                    eventsWrap.className = 'calendar-events';

                    dayBookings.forEach(function (booking) {
                        const eventLine = document.createElement('div');
                        eventLine.className = 'calendar-event';
                        eventLine.title = `${booking.start_time}-${booking.end_time} | ${booking.booked_by} | ${booking.event_name} (${booking.hall_name})`;
                        eventLine.textContent = `${booking.start_time}-${booking.end_time} - ${booking.booked_by} - ${booking.event_name}`;
                        eventsWrap.appendChild(eventLine);
                    });

                    cell.appendChild(eventsWrap);
                }

                cell.addEventListener('click', function () {
                    openDayModal(dayKey, dayBookings);
                });

                if (weekday === 0 || weekday === 6) {
                    cell.classList.add('is-weekend');
                }

                if (
                    day === now.getDate() &&
                    month === now.getMonth() &&
                    year === now.getFullYear()
                ) {
                    cell.classList.add('is-today');
                }

                fragment.appendChild(cell);
            }

            const totalCells = firstDay + daysInCurrentMonth;
            const trailingDays = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);

            for (let day = 1; day <= trailingDays; day++) {
                const cell = document.createElement('div');
                cell.className = 'calendar-day is-other-month';
                cell.textContent = day;
                fragment.appendChild(cell);
            }

            daysContainer.appendChild(fragment);
        }

        prevButton.addEventListener('click', function () {
            visibleMonth -= 1;
            if (visibleMonth < 0) {
                visibleMonth = 11;
                visibleYear -= 1;
            }
            renderCalendar(visibleYear, visibleMonth);
        });

        nextButton.addEventListener('click', function () {
            visibleMonth += 1;
            if (visibleMonth > 11) {
                visibleMonth = 0;
                visibleYear += 1;
            }
            renderCalendar(visibleYear, visibleMonth);
        });

        todayButton.addEventListener('click', function () {
            visibleYear = now.getFullYear();
            visibleMonth = now.getMonth();
            renderCalendar(visibleYear, visibleMonth);
        });

        renderCalendar(visibleYear, visibleMonth);
    });
</script>
@endsection

