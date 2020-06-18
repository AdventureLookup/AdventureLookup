import * as React from "react";
import { Filters } from "./Filters";
import { createPortal } from "react-dom";
import { SearchBox } from "./SearchBox";
import { SearchTags } from "./SearchTags";

export function Root({
  fields,
  url,
  initialFilterValues,
  initialQuery,
  initialSortBy,
  initialSeed,
  fieldStats,
}) {
  const [query, setQuery] = React.useState(initialQuery);
  const [sortBy, setSortBy] = React.useState(initialSortBy);
  const [seed, setSeed] = React.useState(initialSeed);
  const [filterValues, setFilterValues] = React.useState(initialFilterValues);
  const [isSubmitting, setIsSubmitting] = React.useState(false);
  const onSubmit = React.useCallback(() => setIsSubmitting(true), []);

  const doSubmit = () => {
    let newUrl = `${url}`;
    const addParam = (key, value) => {
      if (value === "" || value === undefined) {
        return;
      }
      if (newUrl.indexOf("?") === -1) {
        newUrl += "?";
      } else {
        newUrl += "&";
      }
      newUrl += `${key}=${encodeURIComponent(value)}`;
    };

    addParam("q", query);
    fields
      .filter((field) => field.availableAsFilter)
      .forEach((field) => {
        const filter = filterValues[field.name];
        switch (field.type) {
          case "integer":
            const args = [];
            if (filter.v.min !== "") {
              args.push(`≥${filter.v.min}`);
            }
            if (filter.v.max !== "") {
              args.push(`≤${filter.v.max}`);
            }
            if (args.length > 0 && filter.includeUnknown === true) {
              args.push("unknown");
            }
            addParam(field.name, args.join("~"));
            break;
          case "string": {
            const args = filter.v.map((value) => value.replace(/~/g, "~~"));
            if (filter.includeUnknown) {
              args.push("unknown~");
            }
            addParam(field.name, args.join("~"));
            break;
          }
          case "boolean":
            let value = filter.v;
            if (value !== "" && filter.includeUnknown) {
              value += "~unknown";
            }
            addParam(field.name, value);
            break;
          case "text":
          case "url":
          default:
            throw new Error(`Unknown field type ${field.type}`);
        }
      });
    addParam("sortBy", sortBy);
    addParam("seed", seed);

    document.location.href = newUrl;
  };

  React.useEffect(() => {
    if (!isSubmitting) {
      return;
    }
    doSubmit();
  }, [isSubmitting, doSubmit]);

  // Automatically submit the form whenever sortBy or the seed changes.
  React.useEffect(() => {
    if (sortBy !== initialSortBy || seed !== initialSeed) {
      onSubmit();
    }
  }, [sortBy, seed]);

  return (
    <>
      <div className="content">
        <a className="sidebar-title" href={url}>
          Adventure Lookup
        </a>
        <Filters
          fields={fields}
          initialFilterValues={initialFilterValues}
          filterValues={filterValues}
          setFilterValues={setFilterValues}
          fieldStats={fieldStats}
          onSubmit={onSubmit}
        />
      </div>
      {createPortal(
        <>
          <SearchBox
            query={query}
            onQueryChanged={setQuery}
            sortBy={sortBy}
            onSortByChanged={setSortBy}
            isSubmitting={isSubmitting}
            onSubmit={onSubmit}
            setSeed={setSeed}
          />
          <SearchTags
            // Pass initialFilterValues, not filterValues, so that the search tags always reflect the filters
            // that were used for the current search results
            initialFilterValues={initialFilterValues}
            setFilterValues={setFilterValues}
            fields={fields}
            onSubmit={onSubmit}
          />
        </>,
        document.getElementById("search-results-header-react-root")
      )}
    </>
  );
}
