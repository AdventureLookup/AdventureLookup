import * as React from "react";

export function SearchTags({ initialFilterValues, fields, onSubmit }) {
  return (
    <div id="search-tags">
      {Object.entries(initialFilterValues).map(([fieldName, filter]) => {
        let values = filter.v ?? [];
        if (!Array.isArray(values) && typeof values !== "object") {
          values = [values];
        }

        const field = fields.find((field) => field.name === fieldName);
        return Object.entries(values)
          .filter(([key, value]) => value !== "")
          .map(([key, value]) => {
            const remove = () => {
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
                $(`input[name^="f[${field.name}][v]"][value=""]`).prop(
                  "checked",
                  true
                );
              } else if (field.type === "integer") {
                $(`input[name^="f[${field.name}][v][${key}]"]`).val("");
              }
              onSubmit();
            };

            return (
              <span
                key={key}
                className="badge badge-primary filter-tag"
                onClick={() => remove()}
                title="remove filter"
              >
                {field.title}:{" "}
                {field.type === "boolean" && (value === "1" ? "yes" : "no")}
                {field.type === "integer" && (key == "min" ? "≥" : "≤") + value}
                {field.type === "string" && value}{" "}
              </span>
            );
          });
      })}
    </div>
  );
}
