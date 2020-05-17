import * as React from "react";

const visibleFieldNames = [
  "publisher",
  "setting",
  "edition",
  "environments",
  "items",
  "bossMonsters",
  "commonMonsters",
  "numPages",
  "minStartingLevel",
  "maxStartingLevel",
  "startingLevelRange",
  "soloable",
  "pregeneratedCharacters",
  "handouts",
  "tacticalMaps",
  "partOf",
  "foundIn",
  "year"
];

function isFilterValueEmpty(field, value) {
  switch (field.type) {
    case "string":
    case "boolean":
      return value === "" || value === undefined;
    case "integer":
      return value === undefined || (value.min === "" && value.max === "");
  }
}

function areSetsEqual(a, b) {
  if (a.size !== b.size) return false;
  for (const entry of a) {
    if (!b.has(entry)) return false;
  }
  return true;
}

export const Filters = React.memo(function Filters({
  fields,
  showMoreFilters,
  initialFilterValues,
  fieldStats,
  onSubmit,
}) {
  const showMoreAfter = 13;
  return fields.map((field, i) => (
    <FieldFilter
      key={field.name}
      field={field}
      initialFilter={initialFilterValues[field.name] ?? {}}
      fieldValues={fieldStats[`vals_${field.name}`]}
      visibility={
        !visibleFieldNames.includes(field.name)
          ? "NEVER"
          : i < showMoreAfter ||
            showMoreFilters ||
            !isFilterValueEmpty(field, initialFilterValues[field.name]?.v)
          ? "YES"
          : "SHOW_MORE"
      }
      onSubmit={onSubmit}
    />
  ));
});

function FieldFilter({
  field,
  visibility,
  initialFilter,
  fieldValues,
  onSubmit,
}) {
  const alwaysOpen = field.type === "boolean" || field.type === "integer";
  const filterSet = !isFilterValueEmpty(field, initialFilter?.v);

  const [isOpen, setOpen] = React.useState(filterSet);
  const [isDirty, setIsDirty] = React.useState(false);

  if (visibility === "NEVER") {
    return (
      <input
        type="hidden"
        name={`f[${field.name}][v]`}
        value={initialFilter.v ?? ""}
      />
    );
  }

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
            fieldValues={fieldValues}
            onIsDirty={setIsDirty}
          />
        )}
        {field.type === "boolean" && (
          <BooleanOptions
            field={field}
            initialFilter={initialFilter}
            onIsDirty={setIsDirty}
          />
        )}
        {field.type === "integer" && (
          <IntegerOptions
            field={field}
            initialFilter={initialFilter}
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
}
function filterBuckets(bucket,searchString) {
  const stringToSearch = (bucket.key || '').toLowerCase();
  return stringToSearch.includes(searchString.toLowerCase());
}
function StringOptions({ field, fieldValues, initialFilter, onIsDirty }) {
  // Whether to show the full list of options or only first few.
  const showMoreAfter = 5;
  const [filterString, setFilterString] = React.useState('');
  const [showAll, setShowAll] = React.useState(false);

  // ElasticSearch statistics on which options are available.
  const buckets = fieldValues["buckets"];
  const bucketsWithFilterKey = filterString ? buckets.filter((b) => filterBuckets(b, filterString)) : buckets;
  // Normalize the initial options into an array.
  const initialValues = React.useMemo(() => {
    let initialValues = initialFilter.v || [];
    if (!Array.isArray(initialValues)) {
      if (initialValues === "") {
        initialValues = [];
      } else {
        initialValues = [initialValues];
      }
    }
    return initialValues;
  }, [initialFilter]);

  const [selectedValues, setSelectedValues] = React.useState(initialValues);

  React.useEffect(() => {
    onIsDirty(!areSetsEqual(new Set(initialValues), new Set(selectedValues)));
  }, [selectedValues, initialValues]);

  const valuesUsed = new Set();
  return (
    <>
      <div className="string-options">
      <div className="option">
        <input 
          className="filter-searchbar"
          type="text"
          placeholder="Find Option"
          onChange={(e) => setFilterString(e.target.value)} value={filterString}
          title="Find Option"
        />
      </div>
        {bucketsWithFilterKey.map((bucket, i) => {
          valuesUsed.add(bucket.key);
          return (
            <StringCheckbox
              key={bucket.key}
              field={field}
              value={bucket.key}
              checked={selectedValues.includes(bucket.key)}
              count={bucket.doc_count}
              hidden={!showAll && i >= showMoreAfter}
              onChange={(selected) =>
                selected
                  ? setSelectedValues([...selectedValues, bucket.key])
                  : setSelectedValues(
                      selectedValues.filter((each) => each !== bucket.key)
                    )
              }
            />
          );
        })}
        {initialValues
          .filter((value) => value !== "" && !valuesUsed.has(value))
          .map((value) => (
            <StringCheckbox
              key={value}
              field={field}
              value={value}
              checked={selectedValues.includes(value)}
              count={0}
              hidden={false}
              onChange={(selected) =>
                selected
                  ? setSelectedValues([...selectedValues, value])
                  : setSelectedValues(
                      selectedValues.filter((each) => each !== value)
                    )
              }
            />
          ))}
      </div>
      {bucketsWithFilterKey.length > showMoreAfter && (
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
      {bucketsWithFilterKey.length === 0 && (
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

function StringCheckbox({ field, value, checked, count, hidden, onChange }) {
  return (
    <label
      className={`option${hidden ? " d-none" : ""}${
        checked ? " filter-marked" : ""
      }`}
    >
      <input
        type="checkbox"
        name={`f[${field.name}][v][]`}
        value={value}
        checked={checked}
        onChange={(e) => onChange(e.target.checked)}
      />
      {value}
      <div className="spacer" />
      <span className="badge-pill badge badge-info">{count}</span>
    </label>
  );
}

function BooleanOptions({ field, initialFilter, onIsDirty }) {
  const initialValue = initialFilter.v ?? "";
  const [value, setValue] = React.useState(initialValue);

  return (
    <div className="option">
      <div className="form-check form-check-inline">
        <input
          className="form-check-input"
          type="radio"
          name={`f[${field.name}][v]`}
          value=""
          id={`sidebar-filter-${field.name}-all`}
          checked={value === ""}
          onChange={() => {
            setValue("");
            onIsDirty("" !== initialValue);
          }}
        />
        <label
          className="form-check-label"
          htmlFor={`sidebar-filter-${field.name}-all`}
        >
          All
        </label>
      </div>
      <div className="form-check form-check-inline">
        <input
          className="form-check-input"
          type="radio"
          name={`f[${field.name}][v]`}
          value="1"
          id={`sidebar-filter-${field.name}-yes`}
          checked={value === "1"}
          onChange={() => {
            setValue("1");
            onIsDirty("1" !== initialValue);
          }}
        />
        <label
          className="form-check-label"
          htmlFor={`sidebar-filter-${field.name}-yes`}
        >
          Yes
        </label>
      </div>
      <div className="form-check form-check-inline">
        <input
          className="form-check-input"
          type="radio"
          name={`f[${field.name}][v]`}
          value="0"
          id={`sidebar-filter-${field.name}-no`}
          checked={value === "0"}
          onChange={() => {
            setValue("0");
            onIsDirty("0" !== initialValue);
          }}
        />
        <label
          className="form-check-label"
          htmlFor={`sidebar-filter-${field.name}-no`}
        >
          No
        </label>
      </div>
    </div>
  );
}

function IntegerOptions({ field, initialFilter, onSubmit, onIsDirty }) {
  const initialMin = initialFilter.v?.min ?? "";
  const initialMax = initialFilter.v?.max ?? "";
  const [min, setMin] = React.useState(initialMin);
  const [max, setMax] = React.useState(initialMax);

  return (
    <div className="option range-option">
      <input
        type="number"
        name={`f[${field.name}][v][min]`}
        placeholder="min (inc.)"
        className="form-control form-control-sm"
        value={min}
        onChange={(e) => {
          setMin(e.target.value);
          onIsDirty(e.target.value !== initialMin);
        }}
        title="min (inc.)"
        onKeyPress={(key) => key.which === 13 && onSubmit()}
      />
      <input
        type="number"
        name={`f[${field.name}][v][max]`}
        placeholder="max (inc.)"
        className="form-control form-control-sm"
        value={max}
        onChange={(e) => {
          setMax(e.target.value);
          onIsDirty(e.target.value !== initialMax);
        }}
        title="max (inc.)"
        onKeyPress={(key) => key.which === 13 && onSubmit()}
      />
    </div>
  );
}
