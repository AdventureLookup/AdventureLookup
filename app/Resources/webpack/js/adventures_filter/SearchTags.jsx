import * as React from "react";
import {
  isFilterValueEmpty,
  getEmptyFilter,
  getTagValuesFromFilter,
} from "./field-util";

export function SearchTags({
  initialFilterValues,
  setFilterValues,
  fields,
  onSubmit,
}) {
  const fieldsByName = React.useMemo(() => {
    const result = {};
    fields.forEach((field) => (result[field.name] = field));
    return result;
  }, [fields]);

  const activeFilters = Object.entries(initialFilterValues)
    .map(([fieldName, filter]) => [fieldsByName[fieldName], filter])
    .filter(([field, filter]) => !isFilterValueEmpty(field, filter.v));

  const removeAll = () => {
    activeFilters.forEach(([field]) => {
      setFilterValues((filters) => ({
        ...filters,
        [field.name]: getEmptyFilter(field),
      }));
    });
    onSubmit();
  };

  return (
    <div id="search-tags">
      {activeFilters.map(([field, filter]) => {
        return getTagValuesFromFilter(field, filter).map((tag, i) => {
          return (
            <span
              key={i}
              className="badge badge-primary filter-tag"
              onClick={() => {
                setFilterValues({
                  ...initialFilterValues,
                  [field.name]: tag.without(),
                });
                onSubmit();
              }}
              title="Clear Filter"
            >
              {field.title}: {tag.label}{" "}
            </span>
          );
        });
      })}
      {activeFilters.length > 0 && (
        <span
          className="badge badge-clear"
          onClick={() => removeAll()}
          title="Clear All Filters"
        >
          Clear All Filters{" "}
        </span>
      )}
    </div>
  );
}
