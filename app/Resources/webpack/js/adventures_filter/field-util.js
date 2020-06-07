export function isFilterValueEmpty(field, value) {
  switch (field.type) {
    case "string":
      return value.v.length === 0 && !value.includeUnknown;
    case "boolean":
      return value.v === "";
    case "integer":
      return value.v.min === "" && value.v.max === "";
    case "text":
    case "url":
    default:
      throw new Error(`Unsupported field type ${field.type}.`);
  }
}

export function getEmptyFilter(field) {
  switch (field.type) {
    case "string":
      return {
        v: [],
      };
    case "boolean":
      return {
        v: "",
      };
    case "integer":
      return {
        v: {
          min: "",
          max: "",
        },
      };
    case "text":
    case "url":
    default:
      throw new Error(`Unsupported field type ${field.type}.`);
  }
}

export function getTagValuesFromFilter(field, filter) {
  switch (field.type) {
    case "string": {
      const tags = filter.v.map((value) => ({
        label: value,
        operator: "OR",
        without: (filter) => ({
          ...filter,
          v: filter.v.filter((each) => each !== value),
        }),
      }));
      if (filter.includeUnknown === true) {
        tags.push({
          label: field.multiple ? "none" : "unknown",
          operator: "OR",
          without: (filter) => ({
            ...filter,
            includeUnknown: false,
          }),
        });
      }
      return tags;
    }
    case "boolean":
      if (filter.v === "") {
        return [];
      }
      return [
        {
          label: filter.v === "1" ? "yes" : "no",
          operator: "OR",
          without: (filter) => ({
            ...filter,
            v: "",
          }),
        },
      ];
    case "integer":
      const tags = [];
      if (filter.v.min !== "") {
        tags.push({
          label: `≥ ${filter.v.min}`,
          operator: "AND",
          without: (filter) => ({
            // set includeUnknown to false if both min and max are now ""
            includeUnknown: filter.v.max === "" ? false : filter.includeUnknown,
            v: {
              ...filter.v,
              min: "",
            },
          }),
        });
      }
      if (filter.v.max !== "") {
        tags.push({
          label: `≤ ${filter.v.max}`,
          operator: "AND",
          without: (filter) => ({
            // set includeUnknown to false if both min and max are now ""
            includeUnknown: filter.v.min === "" ? false : filter.includeUnknown,
            v: {
              ...filter.v,
              max: "",
            },
          }),
        });
      }
      if (filter.includeUnknown === true) {
        tags.push({
          label: "unknown",
          operator: "OR",
          without: (filter) => ({
            ...filter,
            includeUnknown: false,
          }),
        });
      }
      return tags;
    case "text":
    case "url":
    default:
      throw new Error(`Unsupported field type ${field.type}.`);
  }
}
