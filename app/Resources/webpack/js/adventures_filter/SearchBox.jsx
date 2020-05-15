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
  setSeed,
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
      <div className="dropdown">
        <button
          className="btn btn-outline-secondary dropdown-toggle"
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
          className="dropdown-menu dropdown-menu-right"
          aria-labelledby="sortButton"
        >
          {sortByOptions.map(({ title, value }) => (
            <a
              key={value}
              className="dropdown-item"
              href="#"
              onClick={(e) => {
                // prevent appending '#' to the URL
                e.preventDefault();
                onSortByChanged(value);
              }}
            >
              {sortBy === value && <i className="fa fa-check mr-1" />}
              {title}
            </a>
          ))}
          <a
            className="dropdown-item"
            href="#"
            onClick={(e) => {
              // prevent appending '#' to the URL
              e.preventDefault();
              // Set a new seed evertime this option is selected. This allows the user to continue to shuffle
              // the adventures if they don't like the current random ordering.
              setSeed(Date.now());
              onSortByChanged("random");
            }}
          >
            {sortBy === "random" && <i className="fa fa-check mr-1" />}
            Random
          </a>
        </div>
      </div>
    </div>
  );
}
