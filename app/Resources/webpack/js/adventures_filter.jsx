import * as React from "react";
import * as ReactDOM from "react-dom";
import { Root } from "./adventures_filter/Root";

(function () {
  if (!$("#search-results").length) {
    return;
  }

  const root = document.getElementById("sidebar-react-root");
  const fields = Object.values(JSON.parse(root.dataset.fields));
  fields.sort((a, b) => b.filterbarSort - a.filterbarSort);
  const initialFilterValues = JSON.parse(root.dataset.initialFilterValues);
  const url = root.dataset.url;
  const initialQuery = root.dataset.initialQuery;
  const initialSortBy = root.dataset.initialSortBy;
  const initialSeed = root.dataset.initialSeed;
  const fieldStats = JSON.parse(root.dataset.fieldStats);
  ReactDOM.render(
    <Root
      fields={fields}
      url={url}
      initialFilterValues={initialFilterValues}
      initialQuery={initialQuery}
      initialSortBy={initialSortBy}
      initialSeed={initialSeed}
      fieldStats={fieldStats}
    />,
    root
  );
})();
