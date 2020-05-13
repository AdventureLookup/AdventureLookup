import * as React from "react";

const sortByOptions = [
  { title: "Best match", value: "" },
  { title: "Reviews", value: "reviews" },
  { title: "Title", value: "title" },
  { title: "Most pages", value: "numPages-desc" },
  { title: "Least pages", value: "numPages-asc" },
  { title: "Recently added", value: "createdAt-desc" },
  { title: "Least recently added", value: "createdAt-asc" },
  // random is special, see below.
];

export function SearchBox({
  query,
  onQueryChanged,
  sortBy,
  onSortByChanged,
  isSubmitting,
  onSubmit,
}) {
  return (
    <div id="search-bar">
      <div className="input-group mr-2">
        <input
          type="text"
          className="form-control"
          placeholder="SEARCH FOR"
          value={query}
          disabled={isSubmitting}
          onChange={(e) => onQueryChanged(e.target.value)}
          onKeyPress={(key) => key.which === 13 && onSubmit()}
        />
        <div className="input-group-append">
          <button
            className="btn btn-primary"
            disabled={isSubmitting}
            onClick={() => onSubmit()}
          >
            Search
          </button>
        </div>
      </div>
      <div class="dropdown">
        <button
          class="btn btn-outline-secondary dropdown-toggle"
          type="button"
          id="sortButton"
          data-toggle="dropdown"
          aria-haspopup="true"
          aria-expanded="false"
          disabled={isSubmitting}
        >
          Sort by
        </button>
        <div
          class="dropdown-menu dropdown-menu-right"
          aria-labelledby="sortButton"
        >
          {sortByOptions.map(({ title, value }) => (
            <a
              class="dropdown-item"
              href="javascript:void(0)"
              onClick={() => onSortByChanged(value)}
            >
              {sortBy === value && <i className="fa fa-check mr-1" />}
              {title}
            </a>
          ))}
          <a
            class="dropdown-item"
            href="javascript:void(0)"
            onClick={() => onSortByChanged(`random-${Date.now()}`)}
          >
            {sortBy.indexOf("random-") === 0 && (
              <i className="fa fa-check mr-1" />
            )}
            Random
          </a>
        </div>
      </div>
    </div>
  );
}
