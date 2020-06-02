export const visibleFieldNames = [
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
  "year",
];

export function isFilterValueEmpty(field, value) {
  switch (field.type) {
    case "text":
    case "url":
      // Not supported
      return true;
    case "string":
      return value.length === 0;
    case "boolean":
      return value === "";
    case "integer":
      return value.min === "" && value.max === "";
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
      // Not supported
      throw new Error(`Unsupported field type ${field.type}.`);
  }
}

export function getTagValuesFromFilter(field, filter) {
  switch (field.type) {
    case "string":
      return filter.v.map((value) => ({
        label: value,
        without: () => ({ v: filter.v.filter((each) => each !== value) }),
      }));
    case "boolean":
      if (filter.v === "") {
        return [];
      }
      return [
        { label: filter.v === "1" ? "yes" : "no", without: () => ({ v: "" }) },
      ];
    case "integer":
      const tags = [];
      if (filter.v.min !== "") {
        tags.push({
          label: `≥ ${filter.v.min}`,
          without: () => ({
            v: {
              ...filter,
              max: "",
            },
          }),
        });
      }
      if (filter.v.max !== "") {
        tags.push({
          label: `≤ ${filter.v.max}`,
          without: () => ({
            v: {
              ...filter,
              min: "",
            },
          }),
        });
      }
      return tags;
    case "text":
    case "url":
      // Not supported
      throw new Error(`Unsupported field type ${field.type}.`);
  }
}
