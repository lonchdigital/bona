(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-menu-builder]').forEach(initializeMenuBuilder);
    });

    function initializeMenuBuilder(root) {
        var form = root.querySelector('[data-menu-form]');
        var dirty = false;
        var activeLocale = resolveInitialLocale(root);

        function resolveInitialLocale(builder) {
            var fallback = builder.dataset.defaultLocale === 'ru' ? 'ru' : 'uk';

            if (builder.dataset.forceDefaultLocale === 'true') {
                return fallback;
            }

            try {
                var stored = window.localStorage.getItem('bona-admin-menu-locale');
                return stored === 'ru' || stored === 'uk' ? stored : fallback;
            } catch (error) {
                return fallback;
            }
        }

        function setLocale(locale) {
            activeLocale = locale === 'ru' ? 'ru' : 'uk';
            root.dataset.activeLocale = activeLocale;

            root.querySelectorAll('[data-menu-locale]').forEach(function (button) {
                var isActive = button.dataset.menuLocale === activeLocale;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            root.querySelectorAll('[data-menu-locale-content]').forEach(function (element) {
                element.hidden = element.dataset.menuLocaleContent !== activeLocale;
            });

            root.querySelectorAll('[data-menu-summary-locale]').forEach(function (element) {
                element.hidden = element.dataset.menuSummaryLocale !== activeLocale;
            });

            root.querySelectorAll('[data-column-summary-locale]').forEach(function (element) {
                element.hidden = element.dataset.columnSummaryLocale !== activeLocale;
            });

            root.querySelectorAll('[data-menu-localized-option]').forEach(function (option) {
                var label = activeLocale === 'ru' ? option.dataset.labelRu : option.dataset.labelUk;
                if (label) {
                    option.textContent = label;
                }
            });

            try {
                window.localStorage.setItem('bona-admin-menu-locale', activeLocale);
            } catch (error) {
                // The editor remains usable when browser storage is unavailable.
            }
        }

        function directItems(list, includeHidden) {
            return Array.prototype.filter.call(list.children, function (child) {
                return child.hasAttribute('data-menu-sort-item') && (includeHidden || !child.hidden);
            });
        }

        function directSortItem(target, list) {
            var item = target.closest('[data-menu-sort-item]');

            while (item && item.parentElement !== list) {
                item = item.parentElement.closest('[data-menu-sort-item]');
            }

            return item && item.parentElement === list ? item : null;
        }

        function updateListEmptyState(list) {
            var empty = Array.prototype.find.call(list.children, function (child) {
                return child.hasAttribute('data-menu-list-empty') || child.hasAttribute('data-header-links-empty');
            });

            if (empty) {
                empty.hidden = directItems(list, false).length > 0;
            }

            var editor = list.closest('[data-footer-menu-editor]');
            if (editor) {
                var counter = editor.querySelector('[data-menu-list-count]');
                if (counter) {
                    counter.textContent = counter.dataset.label + ': ' + directItems(list, true).length;
                }
            }
        }

        function normalizeList(list) {
            var visibleItems = directItems(list, false);
            var hiddenItems = directItems(list, true).filter(function (item) { return item.hidden; });

            visibleItems.concat(hiddenItems).forEach(function (item, index) {
                var orderInput = Array.prototype.find.call(item.children, function (child) {
                    return child.hasAttribute('data-menu-sort-order');
                });

                if (orderInput) {
                    orderInput.value = index;
                }
            });

            if (list.hasAttribute('data-columns-container')) {
                visibleItems.forEach(function (item, index) {
                    var number = item.querySelector('[data-column-number]');
                    if (number) {
                        number.textContent = index + 1;
                    }
                });
            }

            updateListEmptyState(list);
        }

        function normalizeAllLists() {
            root.querySelectorAll('[data-menu-sort-list]').forEach(normalizeList);
            updateSelectedCards();
        }

        function markDirty() {
            if (!form) {
                return;
            }

            dirty = true;
            root.classList.add('is-dirty');
            root.querySelectorAll('[data-menu-dirty-status]').forEach(function (status) {
                status.textContent = status.dataset.dirty;
            });
        }

        function initializeSortable(list) {
            if (list.dataset.menuSortableReady === 'true') {
                return;
            }

            list.dataset.menuSortableReady = 'true';
            var draggedItem = null;

            list.addEventListener('dragstart', function (event) {
                var handle = event.target.closest('[data-menu-drag-handle]');
                if (!handle || handle.closest('[data-menu-sort-list]') !== list) {
                    return;
                }

                var item = directSortItem(handle, list);

                if (!item || item.parentElement !== list || item.hidden) {
                    event.preventDefault();
                    return;
                }

                draggedItem = item;
                item.classList.add('is-dragging');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', 'menu-item');
            });

            list.addEventListener('dragover', function (event) {
                if (!draggedItem || draggedItem.parentElement !== list) {
                    return;
                }

                var target = directSortItem(event.target, list);
                if (!target || target === draggedItem || target.parentElement !== list || target.hidden) {
                    return;
                }

                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
                var rect = target.getBoundingClientRect();
                var horizontal = list.hasAttribute('data-header-links-list');
                var after = horizontal
                    ? event.clientX > rect.left + rect.width / 2
                    : event.clientY > rect.top + rect.height / 2;

                list.insertBefore(draggedItem, after ? target.nextElementSibling : target);
            });

            list.addEventListener('drop', function (event) {
                if (draggedItem) {
                    event.preventDefault();
                }
            });

            list.addEventListener('dragend', function () {
                if (!draggedItem) {
                    return;
                }

                draggedItem.classList.remove('is-dragging');
                draggedItem = null;
                normalizeList(list);
                markDirty();
            });

            list.addEventListener('keydown', function (event) {
                if (event.key !== 'ArrowUp' && event.key !== 'ArrowDown' && event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
                    return;
                }

                var handle = event.target.closest('[data-menu-drag-handle]');
                if (!handle || handle.closest('[data-menu-sort-list]') !== list) {
                    return;
                }

                var item = directSortItem(handle, list);
                if (!item || item.parentElement !== list) {
                    return;
                }

                var items = directItems(list, false);
                var index = items.indexOf(item);
                var moveBack = event.key === 'ArrowUp' || event.key === 'ArrowLeft';
                var targetIndex = moveBack ? index - 1 : index + 1;

                if (targetIndex < 0 || targetIndex >= items.length) {
                    return;
                }

                event.preventDefault();
                if (moveBack) {
                    list.insertBefore(item, items[targetIndex]);
                } else {
                    list.insertBefore(items[targetIndex], item);
                }

                normalizeList(list);
                markDirty();
                handle.focus();
            });
        }

        function initializeAllSortables(scope) {
            scope.querySelectorAll('[data-menu-sort-list]').forEach(initializeSortable);
        }

        function syncVisibility(toggle) {
            var item = toggle.closest('[data-menu-visibility-item]');
            if (item) {
                item.classList.toggle('is-muted', !toggle.checked);
            }
        }

        function syncHeaderLink(toggle, reposition) {
            var list = root.querySelector('[data-header-links-list]');
            var item = root.querySelector('[data-header-link-item="' + toggle.dataset.headerLinkToggle + '"]');
            if (!list || !item) {
                return;
            }

            item.hidden = !toggle.checked;
            if (toggle.checked && reposition) {
                var firstHidden = directItems(list, true).find(function (candidate) {
                    return candidate !== item && candidate.hidden;
                });
                var empty = list.querySelector('[data-header-links-empty]');
                list.insertBefore(item, firstHidden || empty || null);
            } else if (reposition) {
                var emptyNote = list.querySelector('[data-header-links-empty]');
                list.insertBefore(item, emptyNote || null);
            }

            normalizeList(list);
        }

        function updateSelectedCards() {
            var counter = root.querySelector('[data-card-selected-count]');
            if (!counter) {
                return;
            }

            var count = root.querySelectorAll('[data-visual-card-toggle]:checked').length;
            counter.textContent = counter.dataset.label + ': ' + count;
        }

        function syncCategoryFields(select) {
            var item = select.closest('[data-menu-sort-item]');
            if (!item) {
                return;
            }

            var customFields = item.querySelector('[data-menu-custom-link-fields]');
            var autoHint = item.querySelector('[data-menu-category-auto-hint]');
            var hasCategory = select.value !== '';

            if (customFields) {
                customFields.hidden = hasCategory;
            }
            if (autoHint) {
                autoHint.hidden = !hasCategory;
            }
        }

        function nextNumericIndex(elements, attribute) {
            var indexes = Array.prototype.map.call(elements, function (element) {
                return Number(element.getAttribute(attribute));
            }).filter(Number.isFinite);

            return indexes.length ? Math.max.apply(Math, indexes) + 1 : 0;
        }

        function insertBeforeEmpty(list, html) {
            var empty = Array.prototype.find.call(list.children, function (child) {
                return child.hasAttribute('data-menu-list-empty');
            });

            if (empty) {
                empty.insertAdjacentHTML('beforebegin', html);
            } else {
                list.insertAdjacentHTML('beforeend', html);
            }
        }

        root.addEventListener('click', function (event) {
            var localeButton = event.target.closest('[data-menu-locale]');
            if (localeButton) {
                setLocale(localeButton.dataset.menuLocale);
                return;
            }

            var addFooterItem = event.target.closest('[data-footer-menu-add]');
            if (addFooterItem) {
                var editor = addFooterItem.closest('[data-footer-menu-editor]');
                var list = editor.querySelector('[data-footer-menu-list]');
                var template = editor.querySelector('[data-footer-menu-template]');
                var index = nextNumericIndex(list.querySelectorAll('[data-footer-menu-row]'), 'data-index');
                insertBeforeEmpty(list, template.innerHTML.split('__INDEX__').join(String(index)));
                normalizeList(list);
                setLocale(activeLocale);
                markDirty();
                var newRow = list.querySelector('[data-footer-menu-row][data-index="' + index + '"]');
                var input = newRow ? newRow.querySelector('[data-menu-locale-content="' + activeLocale + '"] input') : null;
                if (input) {
                    input.focus();
                }
                return;
            }

            var addColumn = event.target.closest('[data-add-column]');
            if (addColumn) {
                var columns = root.querySelector('[data-columns-container]');
                var columnTemplate = root.querySelector('#catalog-menu-column-template');
                var columnIndex = nextNumericIndex(columns.querySelectorAll('[data-column-index]'), 'data-column-index');
                insertBeforeEmpty(columns, columnTemplate.innerHTML.split('__COLUMN__').join(String(columnIndex)));
                initializeAllSortables(columns);
                normalizeList(columns);
                setLocale(activeLocale);
                markDirty();
                var newColumn = columns.querySelector('[data-column-index="' + columnIndex + '"]');
                var titleInput = newColumn ? newColumn.querySelector('[data-menu-locale-content="' + activeLocale + '"] input') : null;
                if (titleInput) {
                    titleInput.focus();
                }
                return;
            }

            var addItem = event.target.closest('[data-add-item]');
            if (addItem) {
                var column = addItem.closest('[data-column-index]');
                var items = column.querySelector('[data-items-container]');
                var itemTemplate = root.querySelector('#catalog-menu-item-template');
                var itemIndex = nextNumericIndex(items.querySelectorAll('[data-item-index]'), 'data-item-index');
                var html = itemTemplate.innerHTML
                    .split('__COLUMN__').join(column.dataset.columnIndex)
                    .split('__ITEM__').join(String(itemIndex));
                insertBeforeEmpty(items, html);
                normalizeList(items);
                setLocale(activeLocale);
                var newItem = items.querySelector('[data-item-index="' + itemIndex + '"]');
                var select = newItem ? newItem.querySelector('[data-menu-category-select]') : null;
                if (select) {
                    syncCategoryFields(select);
                    select.focus();
                }
                markDirty();
                return;
            }

            var removeButton = event.target.closest('[data-menu-remove]');
            if (removeButton) {
                var removable = removeButton.closest('[data-menu-removable]');
                var parentList = removable ? removable.parentElement : null;
                if (removable) {
                    removable.remove();
                }
                if (parentList && parentList.hasAttribute('data-menu-sort-list')) {
                    normalizeList(parentList);
                }
                markDirty();
                return;
            }

            var collapseButton = event.target.closest('[data-menu-collapse]');
            if (collapseButton) {
                var collapseColumn = collapseButton.closest('[data-column-index]');
                var body = collapseColumn.querySelector('[data-menu-collapsible-body]');
                var expanded = collapseButton.getAttribute('aria-expanded') === 'true';
                body.hidden = expanded;
                collapseButton.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                collapseButton.setAttribute('aria-label', expanded ? collapseButton.dataset.expandLabel : collapseButton.dataset.collapseLabel);
                var icon = collapseButton.querySelector('.fe');
                if (icon) {
                    icon.classList.toggle('fe-chevron-up', !expanded);
                    icon.classList.toggle('fe-chevron-down', expanded);
                }
            }
        });

        root.addEventListener('change', function (event) {
            if (event.target.matches('[data-menu-visibility-toggle]')) {
                syncVisibility(event.target);
                updateSelectedCards();
            }

            if (event.target.matches('[data-header-link-toggle]')) {
                syncHeaderLink(event.target, true);
            }

            if (event.target.matches('[data-menu-category-select]')) {
                syncCategoryFields(event.target);
            }

            if (form && form.contains(event.target)) {
                markDirty();
            }
        });

        root.addEventListener('input', function (event) {
            if (event.target.matches('[data-menu-summary-input]')) {
                var row = event.target.closest('[data-footer-menu-row]');
                var summary = row ? row.querySelector('[data-menu-summary-locale="' + event.target.dataset.menuSummaryInput + '"]') : null;
                if (summary) {
                    summary.textContent = event.target.value.trim() || summary.dataset.emptyLabel;
                }
            }

            if (event.target.matches('[data-column-summary-input]')) {
                var column = event.target.closest('[data-column-index]');
                var columnSummary = column ? column.querySelector('[data-column-summary-locale="' + event.target.dataset.columnSummaryInput + '"]') : null;
                if (columnSummary) {
                    columnSummary.textContent = event.target.value.trim() || columnSummary.dataset.emptyLabel;
                }
            }

            if (form && form.contains(event.target)) {
                markDirty();
            }
        });

        root.querySelectorAll('[data-menu-visibility-toggle]').forEach(syncVisibility);
        root.querySelectorAll('[data-header-link-toggle]').forEach(function (toggle) {
            syncHeaderLink(toggle, false);
        });
        root.querySelectorAll('[data-menu-category-select]').forEach(syncCategoryFields);
        initializeAllSortables(root);
        normalizeAllLists();
        setLocale(activeLocale);

        if (form) {
            form.addEventListener('submit', function () {
                normalizeAllLists();
                dirty = false;
                root.classList.remove('is-dirty');
                root.querySelectorAll('[data-menu-dirty-status]').forEach(function (status) {
                    status.textContent = status.dataset.saving || status.dataset.clean;
                });
            });

            window.addEventListener('beforeunload', function (event) {
                if (!dirty) {
                    return;
                }

                event.preventDefault();
                event.returnValue = root.dataset.unsavedWarning;
            });
        }
    }
})();
