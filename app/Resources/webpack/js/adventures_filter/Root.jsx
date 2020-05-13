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
  const [showMoreFilters, setShowMoreFilters] = React.useState(false);
  const [query, setQuery] = React.useState(initialQuery);
  const [sortBy, setSortBy] = React.useState(initialSortBy);
  const [isSubmitting, setIsSubmitting] = React.useState(false);
  const [seed, setSeed] = React.useState(initialSeed);
  const formRef = React.useRef(null);

  const onSubmit = () => {
    if (!formRef.current) {
      return;
    }
    setIsSubmitting(true);
    formRef.current.submit();
  };

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
        <form method="post" action={url} id="search-form" ref={formRef}>
          <input type="hidden" value={query} name="q" />
          <input type="hidden" value={sortBy} name="sortBy" />
          <input type="hidden" value={seed} name="seed" />
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
            onQueryChanged={setQuery}
            sortBy={sortBy}
            onSortByChanged={setSortBy}
            isSubmitting={isSubmitting}
            onSubmit={onSubmit}
            setSeed={setSeed}
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
