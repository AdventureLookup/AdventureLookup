import * as React from "react";

export function SearchBox({ query, onQueryChanged, onSubmit }) {
  const [isSearching, setIsSearching] = React.useState(false);

  const search = () => {
    setIsSearching(true);
    onSubmit(query);
  };

  return (
    <div id="search-bar">
      <input
        type="text"
        id="search-query"
        placeholder="SEARCH FOR"
        value={query}
        disabled={isSearching}
        onChange={(e) => onQueryChanged(e.target.value)}
        onKeyPress={(key) => key.which === 13 && search()}
      />
      <button
        id="search-submit"
        className="btn btn-primary"
        disabled={isSearching}
        onClick={() => search()}
      >
        Search
      </button>
    </div>
  );
}
