function addRemoveRowValue(value, name) {
    const input = domEl(`input[type="hidden"][name="${name}"]`);
    const table = domEl(`.bw-table.${name}.selectable`);
    const checkAllBox = table.querySelector('th:first-child input[type="checkbox"]');
    const partiallyCheckedBox = table.querySelector('th:first-child .check-icon');
    const totalRows = table.getAttribute('data-total-rows') * 1;
    let totalChecked = table.getAttribute('data-total-checked') * 1;
    if (value) {
        if (input.value.includes(value)) { // remove
            const keyword = `(,?)${value}`;
            input.value = input.value.replace(input.value.match(keyword)[0], '');
            totalChecked--;
        } else { // add
            input.value += `,${value}`;
            totalChecked++;
        }
        table.setAttribute('data-total-checked', `${totalChecked}`);
        if (totalChecked > 0 && totalChecked < totalRows) {
            hide(checkAllBox, true);
            unhide(partiallyCheckedBox, true);
            if (!partiallyCheckedBox.getAttribute('onclick')) {
                partiallyCheckedBox.setAttribute('onclick', `checkAllFromPartiallyChecked('${name}')`);
            }
        } else {
            unhide(checkAllBox, true);
            hide(partiallyCheckedBox, true);
            checkAllBox.checked = (totalChecked === totalRows);
        }
        stripComma(input);
    }
}

function checkAllFromPartiallyChecked(name) {
    const table = domEl(`.bw-table.${name}.selectable`);
    const checkAllBox = table.querySelector('th:first-child input[type="checkbox"]')
    checkAllBox.checked = true;
    toggleAll(checkAllBox, `.bw-table.${name}`);
}

function listenToSelectableTableRowEvents(name) {
    domEls(`.bw-table.${name}.selectable tr`).forEach((el) => {
        el.addEventListener('click', (e) => {
            el.classList.toggle('selected');
            let id = el.getAttribute('data-id');
            let checkbox = el.querySelector('td:first-child input[type="checkbox"]');
            if (checkbox) checkbox.checked = el.classList.contains('selected');
            addRemoveRowValue(id, name);
        });
    });
}

function addCheckboxesToTable(el) {
    let table = domEl(el);
    let checkboxHtml = domEl('.checkbox-template').innerHTML;
    let partialCheckHtml = domEl('.partial-check-template').innerHTML;

    for (let row of table.rows) {
        let cellTag = (row.parentElement.tagName.toLowerCase() === 'thead') ? 'th' : 'td';
        let checkboxCell = document.createElement(cellTag);
        checkboxCell.innerHTML = (cellTag === 'th') ?
            checkboxHtml.replace('type="checkbox"', `type="checkbox" onclick="toggleAll(this,'${el}')"`) +
            partialCheckHtml :
            checkboxHtml;
        checkboxCell.setAttribute('class', '!size-0 !pr-0');
        row.insertBefore(checkboxCell, row.firstChild);
    }
    table.setAttribute('data-total-rows', (table.rows.length - 1)); // minus heading
    table.setAttribute('data-total-checked', 0);
}

function toggleAll(srcEl, table) {
    domEls(`${table}.selectable tr`).forEach((el) => {
        const checkbox = el.querySelector('td:first-child input[type="checkbox"]');
        if (checkbox) {
            // to properly take advantage of the logic for adding and removing IDs
            // already defined in addRemoveRowValue(), simply simulate a click of the checkbox
            if (srcEl.checked && !checkbox.checked || (!srcEl.checked && checkbox.checked)) el.click();
        }
    });
}

function checkSelected(table, selectedValue) {
    let selectedValues = selectedValue.split(',');
    domEls(`${table}.selectable tr`).forEach((el) => {
        const thisValue = el.getAttribute('data-id');
        if (selectedValues.includes(thisValue)) {
            el.click();
        }
    });
}

// Save the initial order of all tables when the page loads
function saveOriginalTableOrder() {
    domEls("table.sortable").forEach(table => {
        const tbody = table.tBodies[0];
        const rows = Array.from(tbody.rows);
        originalTableOrder.set(table, rows); // Store original rows for this table
    });
}

function sortTableByColumn(el, table) {
    let sortColumnIndex = el.getAttribute('data-column-index');
    let sortDirection = el.getAttribute('data-sort-dir') || 'no-sort';
    let sortTable = domEl(`.${table}`);
    const tbody = sortTable.tBodies[0];
    let currentPage = String(sortTable.getAttribute('data-current-page') || 1);

    changeColumnSortIcon(sortColumnIndex, table, sortDirection);

    sortDirection = (sortDirection === "no-sort") ? "asc" : ((sortDirection === "asc") ? "desc" : "no-sort");
    let sortColumn = domEl(`.${table} th[data-column-index="${sortColumnIndex}"]`);
    sortColumn.setAttribute('data-sort-dir', sortDirection);

    if (sortDirection === "no-sort") {
        resetToOriginalOrder(sortTable, tbody, currentPage);
    } else {
        const rows = Array.from(tbody.rows).filter(row => (row.getAttribute('data-page') === currentPage));
        document.body.appendChild(tbody);

        rows.forEach(row => {
            const cellValue = row.cells[sortColumnIndex].innerText.trim();
            row.sortKey = isNumeric(cellValue) ? parseFloat(cellValue) : cellValue.toLowerCase();
        });

        rows.sort((a, b) => {
            if (typeof a.sortKey === "number" && typeof b.sortKey === "number") {
                return sortDirection === "asc" ? a.sortKey - b.sortKey : b.sortKey - a.sortKey;
            } else {
                return sortDirection === "asc" ? a.sortKey.localeCompare(b.sortKey) : b.sortKey.localeCompare(a.sortKey);
            }
        });

        rows.forEach(row => tbody.appendChild(row));
        sortTable.appendChild(tbody);
    }
}

function changeColumnSortIcon(column, table, direction) {
    resetColumnSortIcons(table);
    if (direction === 'no-sort') {
        hide(`.${table} th[data-column-index="${column}"] svg.no-sort`);
        hide(`.${table} th[data-column-index="${column}"] svg.sort-desc`);
        unhide(`.${table} th[data-column-index="${column}"] svg.sort-asc`);
    }
    if (direction === 'asc') {
        hide(`.${table} th[data-column-index="${column}"] svg.no-sort`);
        hide(`.${table} th[data-column-index="${column}"] svg.sort-asc`);
        unhide(`.${table} th[data-column-index="${column}"] svg.sort-desc`);
    }
    if (direction === 'desc') {
        hide(`.${table} th[data-column-index="${column}"] svg.sort-asc`);
        hide(`.${table} th[data-column-index="${column}"] svg.sort-desc`);
        unhide(`.${table} th[data-column-index="${column}"] svg.no-sort`);
    }
}

function resetColumnSortIcons(table) {
    domEls(`.${table} th[data-can-sort="true"]`).forEach((el) => {
        let column = el.getAttribute('data-column-index');
        hide(`.${table} th[data-column-index="${column}"] svg.sort-desc`);
        hide(`.${table} th[data-column-index="${column}"] svg.sort-asc`);
        unhide(`.${table} th[data-column-index="${column}"] svg.no-sort`);
        el.setAttribute('data-sort-dir', 'no-sort');
    });
}

function resetToOriginalOrder(table, tbody, currentPage) {
    const originalRows = originalTableOrder.get(table);
    const currentPageRows = originalRows.filter(row => (row.getAttribute('data-page') === currentPage));
    currentPageRows.forEach(row => tbody.appendChild(row));
}

/*
 | Sorting is bound by delegation rather than an inline onclick, so a strict CSP
 | does not disable it and headings rendered after load still work. See #608.
 */
bwOn('click', 'th[data-can-sort="true"]', (th) => {
    const table = th.closest('table');
    if (table) sortTableByColumn(th, table.dataset.name);
});

/*
 | The filter bar's input used to carry an inline onInput that reached for the
 | page's tableData_<name> global. Delegated here instead, so a strict CSP does
 | not disable searching. See #608.
 */
bwOn('input', '[data-bw-table-search]', (input) => {
    const name = input.getAttribute('data-bw-table-search');
    const data = window[`tableData_${name.replace(/-/g, '_')}`];

    filterTableDebounced(
        input.value,
        `table.${name}`,
        input.getAttribute('data-bw-search-field'),
        parseInt(input.getAttribute('data-bw-search-debounce'), 10) || 0,
        parseInt(input.getAttribute('data-bw-search-min'), 10) || 0,
        data
    )();
});

