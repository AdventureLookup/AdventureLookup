import * as React from "react";

export function SearchTags({ initialFilterValues, fields, onSubmit }) {
  const activeFilters = Object.entries(initialFilterValues).filter(
    ([fieldName, filter]) => {
      return (
        filter.v &&
        Object.entries(filter.v).filter(([key, value]) => value !== "").length >
          0
      );
    }
  );

  const removeFilter = (field, key, value) => {
    if (field.type === "string") {
      const $strInput = $(
        `input[name^="f[${field.name}][v]"][value="${value}"]`
      );
      if ($strInput.is(":hidden")) {
        $strInput.remove();
      } else {
        $strInput.prop("checked", false);
      }
    } else if (field.type === "boolean") {
      $(`input[name^="f[${field.name}][v]"][value=""]`).prop("checked", true);
    } else if (field.type === "integer") {
      $(`input[name^="f[${field.name}][v][${key}]"]`).val("");
    }
  };

  const removeAll = () => {
    activeFilters.forEach(([fieldName, filter]) => {
      let values = filter.v;
      if (!Array.isArray(values) && typeof values !== "object") {
        values = [values];
      }
      const field = fields.find((field) => field.name === fieldName);
      Object.entries(values)
        .filter(([key, value]) => value !== "")
        .forEach(([key, value]) => removeFilter(field, key, value));
    });
    onSubmit();
  };

  return (
    <div id="search-tags">
      {activeFilters.map(([fieldName, filter]) => {
        let values = filter.v;
        if (!Array.isArray(values) && typeof values !== "object") {
          values = [values];
        }

        const field = fields.find((field) => field.name === fieldName);
        return Object.entries(values)
          .filter(([key, value]) => value !== "")
          .map(([key, value]) => {
            const remove = () => {
              removeFilter(field, key, value);
              onSubmit();
            };

            return (
              <span
                key={key}
                className="badge badge-primary filter-tag"
                onClick={() => remove()}
                title="Clear Filter"
              >
                {field.title}:{" "}
                {field.type === "boolean" && (value === "1" ? "yes" : "no")}
                {field.type === "integer" && (key == "min" ? "≥" : "≤") + value}
                {field.type === "string" && value}{" "}
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
