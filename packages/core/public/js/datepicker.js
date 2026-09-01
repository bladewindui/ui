function initCalendar({
                          inputId,
                          minDate = null,
                          maxDate = null,
                          dateFormat = "yyyy-mm-dd",
                          useRange = true,
                          weekStarts = "sunday"
                      } = {}) {

    const dateInput = document.getElementById(inputId);
    if (!dateInput) {
        console.error(`Input field with id "${inputId}" not found.`);
        return;
    }
    // Guard against a duplicate popup and duplicate document-level listeners
    // when a framework like Livewire re-renders this markup (and therefore
    // re-executes this script) without a full page reload.
    if (dateInput.dataset.bwInitialised === 'true') {
        return;
    }
    dateInput.dataset.bwInitialised = 'true';
    if (minDate && !(minDate instanceof Date)) {
        minDate = new Date(minDate);
    }
    if (maxDate && !(maxDate instanceof Date)) {
        maxDate = new Date(maxDate);
    }

    const datePicker = document.createElement("div");
    datePicker.classList.add(`bw-datepicker`, `${inputId}`, "hidden");
    datePicker.setAttribute("role", "dialog");
    datePicker.setAttribute("aria-modal", "true");
    document.body.appendChild(datePicker);

    let startDate = null;
    let endDate = null;
    let currentDate = new Date();
    let yearView = false;
    let monthView = false;
    let header = null;
    let title = null;
    let prevButton = null;
    let nextButton = null;

    function positionCalendar() {
        const inputRect = dateInput.getBoundingClientRect();
        const datePickerRect = datePicker.getBoundingClientRect();
        const viewportHeight = window.innerHeight;
        const viewportWidth = window.innerWidth;

        if (inputRect.bottom + datePickerRect.height > viewportHeight && inputRect.top > datePickerRect.height) {
            datePicker.style.top = `${window.scrollY + inputRect.top - datePickerRect.height}px`;
        } else {
            datePicker.style.top = `${window.scrollY + inputRect.bottom}px`;
        }

        if (inputRect.left + datePickerRect.width > viewportWidth) {
            datePicker.style.left = `${window.scrollX + inputRect.right - datePickerRect.width}px`;
        } else {
            datePicker.style.left = `${window.scrollX + inputRect.left}px`;
        }

        datePicker.style.position = "absolute";
        datePicker.style.zIndex = "9999";
    }

    function formatDate(date) {
        if (!date) return "";

        const day = date.getDate(); // 1-31
        const dayPadded = day.toString().padStart(2, "0"); // 01-31
        const monthIndex = date.getMonth(); // 0-11
        const monthPadded = (monthIndex + 1).toString().padStart(2, "0"); // 01-12
        const year = date.getFullYear(); // YYYY
        const yearShort = year.toString().slice(-2); // YY
        const dayShort = date.toLocaleString("default", {weekday: "short"}); // Mon, Tue...
        const monthShort = date.toLocaleString("default", {month: "short"}); // Jan, Feb...

        // **1. Use full unique placeholders**
        let formattedDate = dateFormat
            .replace(/\bD\b/g, "__DAY__")    // Short weekday (Mon, Tue)
            .replace(/\bM\b/g, "__MONTH__")  // Short month (Jan, Feb)
            .replace(/\bY\b/g, "__YEAR__")   // Year placeholder

            .replace(/\bdd\b/g, "__DD__")    // Padded day (01, 02)
            .replace(/\bd\b/g, "__D__")      // Day (1, 2)
            .replace(/\bmm\b/g, "__MM__")    // Padded month (01, 02)
            .replace(/\bm\b/g, "__M__")      // Month (1, 2)
            .replace(/\byyyy\b/g, "__YYYY__") // Full year (2025)
            .replace(/\byy\b/g, "__YY__");    // Short year (25)

        // **2. Replace placeholders with actual values**
        formattedDate = formattedDate
            .replace("__DAY__", dayShort)
            .replace("__MONTH__", monthShort)
            .replace("__YEAR__", year)
            .replace("__DD__", dayPadded)
            .replace("__D__", day)
            .replace("__MM__", monthPadded)
            .replace("__M__", monthIndex + 1)
            .replace("__YYYY__", year)
            .replace("__YY__", yearShort);

        return formattedDate;
    }

    function renderCalendar() {
        renderHeader('', true);
        prevButton.addEventListener("click", (e) => {
            e.stopPropagation();
            if (yearView) {
                currentDate.setFullYear(currentDate.getFullYear() - 10);
            } else if (monthView) {
                currentDate.setFullYear(currentDate.getFullYear() - 1);
            } else {
                currentDate.setMonth(currentDate.getMonth() - 1);
            }
            renderCalendar();
        });

        nextButton.addEventListener("click", (e) => {
            e.stopPropagation();
            if (yearView) {
                currentDate.setFullYear(currentDate.getFullYear() + 10);
            } else if (monthView) {
                currentDate.setFullYear(currentDate.getFullYear() + 1);
            } else {
                currentDate.setMonth(currentDate.getMonth() + 1);
            }
            renderCalendar();
        });

        domEl(`.${inputId} .month`).addEventListener("click", (e) => {
            e.stopPropagation();
            monthView = true;
            renderMonths();
        });

        domEl(`.${inputId} .year`).addEventListener("click", (e) => {
            e.stopPropagation();
            yearView = true;
            renderYears();
        });

        if (!yearView && !monthView) {
            renderDays();
        }
        positionCalendar();
    }

    function renderDays() {
        const table = document.createElement("table");

        const daysOfWeek = weekStarts === "monday"
            ? [DAY_NAMES.mon, DAY_NAMES.tue, DAY_NAMES.wed, DAY_NAMES.thu, DAY_NAMES.fri, DAY_NAMES.sat, DAY_NAMES.sun]
            : [DAY_NAMES.sun, DAY_NAMES.mon, DAY_NAMES.tue, DAY_NAMES.wed, DAY_NAMES.thu, DAY_NAMES.fri, DAY_NAMES.sat];

        const headerRow = document.createElement("tr");
        daysOfWeek.forEach(day => {
            const th = document.createElement("th");
            th.textContent = day;
            headerRow.appendChild(th);
        });
        table.appendChild(headerRow);

        const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1).getDay();
        const daysInMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).getDate();

        let row = document.createElement("tr");
        let offset = weekStarts === "monday" ? (firstDay === 0 ? 6 : firstDay - 1) : firstDay;

        for (let i = 0; i < offset; i++) {
            const cell = document.createElement("td");
            row.appendChild(cell);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const cell = document.createElement("td");
            const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
            cell.textContent = day;
            cell.classList.add("a-day", "hover:bg-primary-200", "dark:hover:text-dark-200", "dark:hover:bg-dark-600");

            if ((minDate && date.getTime() < minDate.setHours(0, 0, 0, 0)) ||
                (maxDate && date.getTime() > maxDate.setHours(23, 59, 59, 999))) {
                cell.classList.add("disabled");
            } else {
                cell.addEventListener("click", (e) => {
                    e.stopPropagation();
                    selectDate(date);
                });
            }

            if (startDate instanceof Date && date.getTime() === startDate.getTime()) {
                cell.classList.add("selected", "bg-primary-600");
            }

            if (endDate instanceof Date && date.getTime() === endDate.getTime()) {
                cell.classList.add("selected", "bg-primary-600");
            }

            if (startDate instanceof Date && endDate instanceof Date && date > startDate && date < endDate) {
                cell.classList.add("in-range", "bg-primary-200/70", "border-primary-50");
            }

            row.appendChild(cell);

            if ((offset + day) % 7 === 0 || day === daysInMonth) {
                table.appendChild(row);
                row = document.createElement("tr");
            }
        }

        datePicker.appendChild(table);
    }

    function renderMonths() {
        renderHeader(currentDate.getFullYear());

        prevButton.addEventListener("click", (e) => {
            e.stopPropagation();
            currentDate.setFullYear(currentDate.getFullYear() - 1);
            renderMonths();
        });

        nextButton.addEventListener("click", (e) => {
            e.stopPropagation();
            currentDate.setFullYear(currentDate.getFullYear() + 1);
            renderMonths();
        });

        const months = [
            MONTH_NAMES.jan,
            MONTH_NAMES.feb,
            MONTH_NAMES.mar,
            MONTH_NAMES.apr,
            MONTH_NAMES.may,
            MONTH_NAMES.jun,
            MONTH_NAMES.jul,
            MONTH_NAMES.aug,
            MONTH_NAMES.sep,
            MONTH_NAMES.oct,
            MONTH_NAMES.nov,
            MONTH_NAMES.dec
        ];

        const container = document.createElement("div");
        container.className = "month-container grid grid-cols-3 divide-x divide-y border dark:border-dark-600/30 dark:divide-dark-600/30 dark:divide-dark-600/30 text-center -mx-2 -mb-2 radius-b-lg";

        months.forEach((month, index) => {
            const date = new Date(currentDate.getFullYear(), index, 1);
            const monthEl = document.createElement("div");
            monthEl.className = "a-month";
            monthEl.textContent = month;
            if ((minDate && date.getFullYear() === minDate.getFullYear() && date.getMonth() < minDate.getMonth()) ||
                (maxDate && date.getFullYear() === maxDate.getFullYear() && date.getMonth() > maxDate.getMonth())) {
                monthEl.classList.add("disabled");
            } else {
                monthEl.addEventListener("click", (e) => {
                    e.stopPropagation();
                    currentDate.setMonth(index);
                    monthView = false;
                    renderCalendar();
                });
            }
            container.appendChild(monthEl);
        });

        datePicker.appendChild(container);
    }

    function renderHeader(heading, isFullCalendar = false) {
        datePicker.innerHTML = "";
        header = document.createElement("div");
        nextButton = document.createElement("span");
        prevButton = document.createElement("span");
        title = document.createElement("div");
        header.className = "datepicker-header bg-primary-600";
        nextButton.innerHTML = domEl('.bw-datepicker-right-arrow').innerHTML;
        prevButton.innerHTML = domEl('.bw-datepicker-left-arrow').innerHTML;
        prevButton.classList.add("nav-button");
        nextButton.classList.add("nav-button");
        if (isFullCalendar) {
            let monthKey = currentDate.toLocaleString("en-US", {month: "short"}).toLowerCase();
            let thisMonth = MONTH_NAMES[monthKey] || monthKey;
            title.className = "month-year text-lg tracking-wider cursor-pointer font-light";
            title.innerHTML = `
                  <span class="month opacity-80 hover:opacity-100">${currentDate.toLocaleString("default", {month: "long"})}</span>
                  <span class="year opacity-80 hover:opacity-100">${currentDate.getFullYear()}</span>`;
        } else {
            title.className = "calendar-title text-lg tracking-wider font-light";
            title.textContent = heading;
        }

        header.appendChild(prevButton);
        header.appendChild(title);
        header.appendChild(nextButton);
        datePicker.appendChild(header);
    }

    function renderYears() {
        const startYear = Math.floor(currentDate.getFullYear() / 12) * 12;
        renderHeader(`${startYear - 1} - ${startYear + 10}`);

        prevButton.addEventListener("click", (e) => {
            e.stopPropagation();
            currentDate.setFullYear(currentDate.getFullYear() - 12);
            renderYears();
        });

        nextButton.addEventListener("click", (e) => {
            e.stopPropagation();
            currentDate.setFullYear(currentDate.getFullYear() + 12);
            renderYears();
        });

        const container = document.createElement("div");
        container.className = "year-container grid grid-cols-4 divide-x divide-y border dark:border-dark-600/30 dark:divide-dark-600/30 text-center -mx-2 -mb-2 radius-b-lg";

        for (let i = startYear - 1; i <= startYear + 10; i++) {
            const yearEl = document.createElement("div");
            yearEl.className = "a-year";
            yearEl.textContent = i;

            if ((minDate && i < minDate.getFullYear()) || (maxDate && i > maxDate.getFullYear())) {
                yearEl.classList.add("disabled");
            } else {
                yearEl.addEventListener("click", (e) => {
                    e.stopPropagation();
                    currentDate.setFullYear(i);
                    yearView = false;
                    renderCalendar();
                });
            }

            container.appendChild(yearEl);
        }

        datePicker.appendChild(container);
    }

    function selectDate(date) {
        if (!(date instanceof Date)) return;
        if ((minDate && date < minDate) || (maxDate && date > maxDate)) {
            return;
        }

        if (!useRange) {
            startDate = date;
            endDate = null;
            datePicker.classList.add("hidden");
        } else if (!startDate || (startDate && endDate)) {
            startDate = date;
            endDate = null;
        } else {
            endDate = date >= startDate ? date : startDate;
            startDate = date < startDate ? date : startDate;
        }

        setFieldValue(dateInput, endDate
            ? `${formatDate(startDate)} - ${formatDate(endDate)}`
            : formatDate(startDate));

        renderCalendar();
    }

    dateInput.addEventListener("click", (e) => {
        e.stopPropagation();
        document.querySelectorAll(".bw-datepicker").forEach(picker => {
            if (picker !== datePicker) {
                picker.classList.add("hidden");
            }
        });

        if (datePicker.classList.contains("hidden")) {
            monthView = false;
            yearView = false;
            renderCalendar();
            datePicker.classList.remove("hidden");
            datePicker.setAttribute("aria-hidden", "false");
            datePicker.setAttribute("tabindex", "0");
            datePicker.focus();
        } else {
            datePicker.classList.add("hidden");
            datePicker.setAttribute("aria-hidden", "true");
        }
    });

    document.addEventListener("click", (e) => {
        if (!datePicker.contains(e.target) && e.target !== dateInput) {
            datePicker.classList.add("hidden");
            datePicker.setAttribute("aria-hidden", "true");
        }
    });

    renderCalendar();
}