import * as React from "react";
import * as ReactDOM from "react-dom";
import { Root } from "./adventures_filter/Root";

(function () {
  if (!$("#search-results").length) {
    return;
  }

  const root = document.getElementById("sidebar-react-root");
  const fieldData = Object.values(JSON.parse(root.dataset.fields));
  const initialFilterValues = JSON.parse(root.dataset.initialFilterValues);
  const url = root.dataset.url;
  const initialQuery = root.dataset.initialQuery;
  const fieldStats = JSON.parse(root.dataset.fieldStats);
  ReactDOM.render(
    <Root
      fields={fieldData}
      url={url}
      initialFilterValues={initialFilterValues}
      initialQuery={initialQuery}
      fieldStats={fieldStats}
    />,
    root
  );
})();
