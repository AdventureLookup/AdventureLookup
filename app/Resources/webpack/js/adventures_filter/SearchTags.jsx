import * as React from "react";

export function SearchTags({
  initialFilterValues,
  setFilterValues,
  fields,
  onSubmit,
}) {
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
                setFilterValues({
                  ...initialFilterValues,
                  [field.name]: {
                    v: initialFilterValues[field.name].v.filter(
                      (each) => each !== value
                    ),
                  },
                });
              } else if (field.type === "boolean") {
                setFilterValues({
                  ...initialFilterValues,
                  [field.name]: {
                    v: "",
                  },
                });
              } else if (field.type === "integer") {
                setFilterValues({
                  ...initialFilterValues,
                  [field.name]: {
                    v: {
                      ...initialFilterValues[field.name].v,
                      [key]: "",
                    },
                  },
                });
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
