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
  fieldStats,
}) {
  const [showMoreFilters, setShowMoreFilters] = React.useState(false);
  const [query, setQuery] = React.useState(initialQuery);
  const formRef = React.useRef(null);

  const onSubmit = () => formRef.current.submit();

  return (
    <>
      <div className="content">
        <a className="sidebar-title" href={url}>
          Adventure Lookup
        </a>
        <form method="post" action={url} id="search-form" ref={formRef}>
          <input type="hidden" value={query} name="q" />
          <Filters
            fields={fields}
            showMoreFilters={showMoreFilters}
            initialFilterValues={initialFilterValues}
            fieldStats={fieldStats}
            onSubmit={onSubmit}
          />
        </form>
        {!showMoreFilters && (
          <div
            id="filter-more"
            title="show more filters"
            onClick={() => setShowMoreFilters(true)}
          ></div>
        )}
      </div>
      {createPortal(
        <>
          <SearchBox
            query={query}
            onSubmit={onSubmit}
            onQueryChanged={setQuery}
          />
          <SearchTags
            initialFilterValues={initialFilterValues}
            fields={fields}
            onSubmit={onSubmit}
          />
        </>,
        document.getElementById("search-results-header-react-root")
      )}
    </>
  );
}
