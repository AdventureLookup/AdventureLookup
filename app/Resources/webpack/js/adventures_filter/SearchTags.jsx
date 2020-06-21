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
    .filter(([field, filter]) => !isFilterValueEmpty(field, filter));

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
        const tags = getTagValuesFromFilter(field, filter);
        const tagElements = tags.map((tag, i) => {
          return (
            // wrap into an inline-block so that the operator is always in front of the tag,
            // even when wrapped accross multiple lines
            <span key={i} className="d-inline-block">
              {i > 0 && <span className="tag-operator">{tag.operator}</span>}
              <span
                className="badge badge-primary"
                onClick={() => {
                  setFilterValues((initialFilterValues) => {
                    return {
                      ...initialFilterValues,
                      [field.name]: tag.without(
                        initialFilterValues[field.name]
                      ),
                    };
                  });
                  onSubmit();
                }}
                title="Clear Filter"
              >
                {field.title}: {tag.label}{" "}
              </span>
            </span>
          );
        });

        if (tagElements.length > 1) {
          return (
            <div className="tag-wrapper" key={field.name}>
              {tagElements}
            </div>
          );
        }

        return tagElements;
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
