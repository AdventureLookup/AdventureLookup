import * as React from "react";

function filterOption(option, searchString) {
  return option.toLowerCase().includes(searchString.toLowerCase());
}

export function StringFilter({
  field,
  // ElasticSearch statistics based on the search results for `initialFilter`. If a
  // filter option is not part of `fieldValues.buckets`, none of the search results
  // (based on `initialFilter`) use this filter option.
  fieldValues,
  initialFilter,
  filter,
  setFilter,
  onIsDirty,
}) {
  const unknownLabel = field.multiple ? "none" : "unknown";

  // We want to show options in the following order:
  // 1. All options from `initialFilter`
  // 2. Unknown/None iff `initialFilter` is empty AND there are adventure
  //    where the field value is unknown.
  // 3. All options from `fieldValues.buckets` that are not part of `initialFilter`.

  // 1.
  // We deliberately use `initialFilter` instead of `filter` here. We want
  // to display all filters prominently that made up the currently displayed
  // search results. Using `filter` would cause these to show up just fine
  // initially, but move them down the list of filter options once the user
  // deselects them. By using `initialFilter`, the options will only move down
  // the list once the user "applys" the new filters and the page reloads.
  //
  // We also map the initial filters into the same format as fieldValues.buckets
  // to make the code below more consistent.
  const initialOptions = initialFilter.v.map((value) => ({
    key: value,
    doc_count:
      // If `value` is not found in `fieldValues.buckets`,
      // none of the search results use this value => There aren't any
      // search results and it's okay to display `0` as `count`.
      fieldValues.buckets.find((bucket) => bucket.key === value)?.doc_count ??
      0,
  }));

  // 2.
  // We only show the Unknown option if any adventures in the currently displayed
  // search results have a field value of unknown OR the user has specifically
  // selected the option.
  const showUnknownOption =
    (initialOptions.length === 0 && fieldValues.countUnknown > 0) ||
    initialFilter.includeUnknown;

  // 3.
  const additionalOptions = fieldValues.buckets.filter(
    (bucket) => !initialOptions.some((option) => option.key === bucket.key)
  );

  // Displayed options can be filtered to more easily find particular options. This
  // corresponds to what the user types into the filter input box.
  const [filterString, setFilterString] = React.useState("");
  // selectAllState is the state of the checkbox next to the filter input box.
  // It can either be selected (`true`), unselected (`false`), or indeterminate (`"indeterminate"`).
  const [selectAllState, setSelectAllState] = React.useState("indeterminate");

  // Let's re-calculate the available options from the `filterString` now.

  // 1.
  const filteredInitialOptions =
    filterString.length === 0
      ? initialOptions
      : initialOptions.filter((option) =>
          filterOption(option.key, filterString)
        );

  // 2.
  const filteredShowUnknownOption =
    showUnknownOption && filterOption(unknownLabel, filterString);

  // 3.
  const filteredAdditionalOptions =
    filterString.length === 0
      ? additionalOptions
      : additionalOptions.filter((option) =>
          filterOption(option.key, filterString)
        );

  // Initially, only the first `showMoreAfter` `filteredAdditionalOptions` are shown (`showAll` = `false`).
  // We always show all `filteredInitialOptions` though.
  const showMoreAfter = 5;
  const [showAll, setShowAll] = React.useState(false);

  // Check if any options are available, ignoring the filter options input box.
  if (
    initialOptions.length === 0 &&
    !showUnknownOption &&
    additionalOptions.length === 0
  ) {
    return (
      <div className="option">
        <em>
          No options available. Remove some search filters to show more options.
        </em>
      </div>
    );
  }

  const onSelectAllCheckboxChange = ({ target: { checked } }) => {
    setSelectAllState(checked);
    setShowAll(true);
    onIsDirty(true);
    if (checked) {
      setFilter((filter) => ({
        ...filter,
        // Set includeUnknown only when the option is visible, leave it untouched otherwise.
        includeUnknown: filteredShowUnknownOption
          ? true
          : filter.includeUnknown,
        // Select all currently selected options and all currently visible options
        // => filter.v UNION filteredInitialOptions UNION filteredAdditionalOptions
        v: [
          ...filter.v,
          ...filteredInitialOptions
            .map((option) => option.key)
            .filter((value) => !filter.v.includes(value)),
          ...filteredAdditionalOptions
            .map((option) => option.key)
            .filter((value) => !filter.v.includes(value)),
        ],
      }));
    } else {
      setFilter((filter) => ({
        ...filter,
        // Set includeUnknown only when the option is visible, leave it untouched otherwise.
        includeUnknown: filteredShowUnknownOption
          ? false
          : filter.includeUnknown,
        // Remove all currently visible options from selected options
        // => filter.v MINUS (filteredInitialOptions UNION filteredAdditionalOptions)
        v: filter.v.filter(
          (each) =>
            !(
              filteredInitialOptions.some((option) => option.key === each) ||
              filteredAdditionalOptions.some((option) => option.key === each)
            )
        ),
      }));
    }
  };

  return (
    <>
      <div className="string-options">
        {/* Option filter bar */}
        <div className="option">
          <input
            type="checkbox"
            title="select all"
            checked={selectAllState === true}
            ref={(input) => {
              if (input) {
                input.indeterminate = selectAllState === "indeterminate";
              }
            }}
            onChange={onSelectAllCheckboxChange}
          />
          <input
            className="filter-searchbar"
            type="text"
            placeholder="Find Option"
            onChange={(e) => {
              setFilterString(e.target.value);
              setSelectAllState("indeterminate");
              setShowAll(true);
            }}
            value={filterString}
            title="Find Option"
          />
        </div>
        {/* 1. Initial options */}
        {filteredInitialOptions.map((option) => (
          <StringCheckbox
            key={option.key}
            label={option.key}
            checked={filter.v.includes(option.key)}
            count={option.doc_count}
            onChange={(selected) => {
              setFilter((filter) => ({
                ...filter,
                v: selected
                  ? [...filter.v, option.key]
                  : filter.v.filter((each) => each !== option.key),
              }));
              setSelectAllState("indeterminate");
              onIsDirty(true);
            }}
          />
        ))}
        {/* 2. Unknown option */}
        {filteredShowUnknownOption && (
          <StringCheckbox
            label={<em>{unknownLabel}</em>}
            checked={filter.includeUnknown}
            count={fieldValues.countUnknown}
            onChange={(selected) => {
              setFilter((filter) => ({
                ...filter,
                includeUnknown: selected,
              }));
              setSelectAllState("indeterminate");
              onIsDirty(true);
            }}
          />
        )}
        {/* 3. Additional options */}
        {filteredAdditionalOptions
          .slice(0, showAll ? undefined : showMoreAfter)
          .map((option) => (
            <StringCheckbox
              key={option.key}
              label={option.key}
              checked={filter.v.includes(option.key)}
              count={option.doc_count}
              onChange={(selected) => {
                setFilter((filter) => ({
                  ...filter,
                  v: selected
                    ? [...filter.v, option.key]
                    : filter.v.filter((each) => each !== option.key),
                }));
                setSelectAllState("indeterminate");
                onIsDirty(true);
              }}
            />
          ))}
      </div>
      {filteredAdditionalOptions.length > showMoreAfter && (
        <>
          {!showAll ? (
            <div
              className="option show-more"
              onClick={() => setShowAll(true)}
              title="show more"
            >
              <i className="fa fa-arrow-down"></i>
            </div>
          ) : (
            <div
              className="option show-less"
              onClick={() => setShowAll(false)}
              title="show less"
            >
              <i className="fa fa-arrow-up"></i>
            </div>
          )}
        </>
      )}
    </>
  );
}

function StringCheckbox({ label, checked, count, onChange }) {
  return (
    <label className="option">
      <input
        type="checkbox"
        checked={checked}
        onChange={(e) => onChange(e.target.checked)}
      />
      {label}
      <div className="spacer" />
      <span className="badge-pill badge badge-primary">{count}</span>
    </label>
  );
}
