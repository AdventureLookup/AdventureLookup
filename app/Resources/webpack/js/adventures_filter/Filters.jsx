import * as React from "react";
import { isFilterValueEmpty } from "./field-util";

export const Filters = React.memo(function Filters({
  fields,
  initialFilterValues,
  filterValues,
  setFilterValues,
  fieldStats,
  onSubmit,
}) {
  const [showMoreFilters, setShowMoreFilters] = React.useState(false);
  const showMoreAfter = 13;

  return (
    <>
      {fields
        .filter((field) => field.availableAsFilter)
        .map((field, i) => {
          // We can useCallback even though we are inside a loop, because
          // fields is a constant. This prevents all filters from
          // re-rendering when a single filter changes.
          const setFilter = React.useCallback(
            (value) => {
              setFilterValues((filterValues) => ({
                ...filterValues,
                [field.name]:
                  typeof value === "function"
                    ? value(filterValues[field.name])
                    : value,
              }));
            },
            [field]
          );
          return (
            <FieldFilter
              key={field.name}
              field={field}
              initialFilter={initialFilterValues[field.name]}
              filter={filterValues[field.name]}
              setFilter={setFilter}
              fieldValues={fieldStats[field.name]}
              visibility={
                i < showMoreAfter ||
                showMoreFilters ||
                !isFilterValueEmpty(field, initialFilterValues[field.name])
                  ? "YES"
                  : "SHOW_MORE"
              }
              onSubmit={onSubmit}
            />
          );
        })}

      {!showMoreFilters && (
        <div
          id="filter-more"
          title="show more filters"
          onClick={() => setShowMoreFilters(true)}
        ></div>
      )}
    </>
  );
});

const FieldFilter = React.memo(function FieldFilter({
  field,
  visibility,
  initialFilter,
  filter,
  setFilter,
  fieldValues,
  onSubmit,
}) {
  const alwaysOpen = field.type === "boolean" || field.type === "integer";
  const filterSet = !isFilterValueEmpty(field, filter);

  const [isOpen, setOpen] = React.useState(filterSet);
  const [isDirty, setIsDirty] = React.useState(false);

  const classes = [];
  if (visibility !== "YES") {
    classes.push("d-none");
  }
  if (isOpen || alwaysOpen) {
    classes.push("open");
  }
  if (alwaysOpen) {
    classes.push("always-open");
  }
  if (filterSet) {
    classes.push("filter-marked");
  }

  const toggleOpen = () => {
    if (!alwaysOpen) {
      setOpen(!isOpen);
    }
  };

  return (
    <div className={`filter ${classes.join(" ")}`}>
      <div className="title" title={field.description} onClick={toggleOpen}>
        {field.title}
      </div>
      <div className="options-list">
        {field.type === "string" && (
          <StringOptions
            field={field}
            initialFilter={initialFilter}
            filter={filter}
            setFilter={setFilter}
            fieldValues={fieldValues}
            onIsDirty={setIsDirty}
          />
        )}
        {field.type === "boolean" && (
          <BooleanOptions
            field={field}
            initialFilter={initialFilter}
            filter={filter}
            setFilter={setFilter}
            fieldValues={fieldValues}
            onIsDirty={setIsDirty}
          />
        )}
        {field.type === "integer" && (
          <IntegerOptions
            field={field}
            initialFilter={initialFilter}
            filter={filter}
            setFilter={setFilter}
            fieldValues={fieldValues}
            onSubmit={onSubmit}
            onIsDirty={setIsDirty}
          />
        )}
        {isDirty && (
          <div className="option apply" onClick={onSubmit}>
            Apply Filter
          </div>
        )}
      </div>
    </div>
  );
});

function filterBuckets(bucket, searchString, selectedValues = []) {
  const stringToSearch = (bucket.key || "").toLowerCase();
  const match = stringToSearch.includes(searchString.toLowerCase());
  const alreadySelected = selectedValues.includes(bucket.key);
  return match || alreadySelected;
}

function StringOptions({
  field,
  fieldValues,
  initialFilter,
  filter,
  setFilter,
  onIsDirty,
}) {
  const values = filter.v;

  // Whether to show the full list of options or only first few.
  const showMoreAfter = 5;
  const [filterString, setFilterString] = React.useState("");
  const [showAll, setShowAll] = React.useState(false);

  // ElasticSearch statistics on which options are available.
  const buckets = fieldValues.buckets;

  const bucketsToShow = filterString
    ? buckets.filter((b) => filterBuckets(b, filterString, values))
    : buckets;

  const showUnknownOption =
    (initialFilter.includeUnknown || fieldValues.countUnknown > 0) &&
    filterString === "";
  const noOptionsAvailable =
    buckets.length === 0 && !showUnknownOption && values.length === 0;

  const valuesUsed = new Set();
  return (
    <>
      <div className="string-options">
        {!noOptionsAvailable && (
          <div className="option">
            <input
              className="filter-searchbar"
              type="text"
              placeholder="Find Option"
              onChange={(e) => setFilterString(e.target.value)}
              value={filterString}
              title="Find Option"
            />
          </div>
        )}
        {showUnknownOption && (
          <StringCheckbox
            field={field}
            label={<em>{field.multiple ? "none" : "unknown"}</em>}
            checked={filter.includeUnknown}
            count={fieldValues.countUnknown}
            hidden={false}
            onChange={(selected) => {
              setFilter((filter) => ({
                ...filter,
                includeUnknown: selected,
              }));
              onIsDirty(true);
            }}
          />
        )}
        {bucketsToShow.map((bucket, i) => {
          valuesUsed.add(bucket.key);
          return (
            <StringCheckbox
              key={bucket.key}
              field={field}
              label={bucket.key}
              checked={values.includes(bucket.key)}
              count={bucket.doc_count}
              hidden={!showAll && i >= showMoreAfter}
              onChange={(selected) => {
                setFilter((filter) => ({
                  ...filter,
                  v: selected
                    ? [...filter.v, bucket.key]
                    : filter.v.filter((each) => each !== bucket.key),
                }));
                onIsDirty(true);
              }}
            />
          );
        })}
        {values
          .filter((value) => !valuesUsed.has(value))
          .map((value) => (
            <StringCheckbox
              key={value}
              field={field}
              label={value}
              checked={true}
              count={0}
              hidden={false}
              onChange={(selected) => {
                setFilter((filter) => ({
                  ...filter,
                  v: selected
                    ? [...filter.v, value]
                    : filter.v.filter((each) => each !== value),
                }));
                onIsDirty(true);
              }}
            />
          ))}
      </div>
      {bucketsToShow.length > showMoreAfter && (
        <>
          {!showAll && (
            <div
              className="option show-more"
              onClick={() => setShowAll(true)}
              title="show more"
            >
              <i className="fa fa-arrow-down"></i>
            </div>
          )}
          {showAll && (
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
      {noOptionsAvailable && (
        <div className="option">
          <em>
            No options available. Remove some search filters to show more
            options.
          </em>
        </div>
      )}
    </>
  );
}

function StringCheckbox({ field, label, checked, count, hidden, onChange }) {
  return (
    <label
      className={`option${hidden ? " d-none" : ""}${
        checked ? " filter-marked" : ""
      }`}
    >
      <input
        type="checkbox"
        checked={checked}
        onChange={(e) => onChange(e.target.checked)}
      />
      {label}
      <div className="spacer" />
      <span className="badge-pill badge badge-info">{count}</span>
    </label>
  );
}

function BooleanOptions({
  field,
  fieldValues,
  initialFilter,
  filter,
  setFilter,
  onIsDirty,
}) {
  // Only display number of adventures that selected "no"/"yes" if the user
  // doesn't filter by this field. If the user filters by all adventures that
  // selected "no", the "yes" bucket is always empty and displaying a 0 for
  // "yes" could be confusing.
  const noCount = initialFilter.v === "" ? fieldValues.countNo : undefined;
  const yesCount = initialFilter.v === "" ? fieldValues.countYes : undefined;
  const allCount = initialFilter.v === "" ? fieldValues.countAll : undefined;

  return (
    <div className="option option-boolean">
      <div className="form-check form-check-inline">
        <input
          className="form-check-input"
          type="radio"
          value=""
          id={`sidebar-filter-${field.name}-all`}
          checked={filter.v === ""}
          onChange={() => {
            setFilter({ ...filter, v: "" });
            onIsDirty(true);
          }}
        />
        <label
          className="form-check-label"
          htmlFor={`sidebar-filter-${field.name}-all`}
        >
          All{" "}
          {allCount !== undefined && (
            <span className="badge-pill badge badge-info">{allCount}</span>
          )}
        </label>
      </div>
      <div className="form-check form-check-inline">
        <input
          className="form-check-input"
          type="radio"
          value="1"
          id={`sidebar-filter-${field.name}-yes`}
          checked={filter.v === "1"}
          onChange={() => {
            setFilter({ ...filter, v: "1" });
            onIsDirty(true);
          }}
        />
        <label
          className="form-check-label"
          htmlFor={`sidebar-filter-${field.name}-yes`}
        >
          Yes{" "}
          {yesCount !== undefined && (
            <span className="badge-pill badge badge-info">{yesCount}</span>
          )}
        </label>
      </div>
      <div className="form-check form-check-inline">
        <input
          className="form-check-input"
          type="radio"
          value="0"
          id={`sidebar-filter-${field.name}-no`}
          checked={filter.v === "0"}
          onChange={() => {
            setFilter({ ...filter, v: "0" });
            onIsDirty(true);
          }}
        />
        <label
          className="form-check-label"
          htmlFor={`sidebar-filter-${field.name}-no`}
        >
          No{" "}
          {noCount !== undefined && (
            <span className="badge-pill badge badge-info">{noCount}</span>
          )}
        </label>
      </div>
    </div>
  );
}

function IntegerOptions({
  field,
  fieldValues,
  initialFilter,
  filter,
  setFilter,
  onSubmit,
  onIsDirty,
}) {
  return (
    <>
      <div className="option option-integer">
        <input
          type="number"
          placeholder="min (inc.)"
          className="form-control form-control-sm"
          value={filter.v.min}
          onChange={(e) => {
            const value = e.target.value;
            setFilter((filter) => ({
              ...filter,
              v: { ...filter.v, min: value },
            }));
            onIsDirty(true);
          }}
          title="min (inc.)"
          onKeyPress={(key) => key.which === 13 && onSubmit()}
        />
        <input
          type="number"
          placeholder="max (inc.)"
          className="form-control form-control-sm"
          value={filter.v.max}
          onChange={(e) => {
            const value = e.target.value;
            setFilter((filter) => ({
              ...filter,
              v: { ...filter.v, max: value },
            }));
            onIsDirty(true);
          }}
          title="max (inc.)"
          onKeyPress={(key) => key.which === 13 && onSubmit()}
        />
      </div>
      {!isFilterValueEmpty(field, filter) && (
        <label
          className="option"
          title="Include adventures where this field is unknown, regardless of the filter set above."
        >
          <input
            type="checkbox"
            onChange={(e) => {
              const value = e.target.checked;
              setFilter((filter) => ({
                ...filter,
                includeUnknown: value,
              }));
              onIsDirty(true);
            }}
            checked={filter.includeUnknown}
          />
          Include when unknown
          <div className="spacer" />
          {(initialFilter.includeUnknown ||
            (initialFilter.v.min === "" && initialFilter.v.max === "")) && (
            <span className="badge-pill badge badge-info">
              {fieldValues.countUnknown}
            </span>
          )}
        </label>
      )}
    </>
  );
}
