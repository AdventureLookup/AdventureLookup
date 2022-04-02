import * as React from "react";
import { InterpretedSearchQuery } from "./InterpretedSearchQuery";

export function SearchTips({ initialParsedQuery }) {
  const [expanded, setExpanded] = React.useState(false);

  if (!expanded) {
    return (
      <a
        id="search-tips"
        href="#"
        onClick={(e) => {
          e.preventDefault();
          setExpanded(true);
        }}
      >
        show search tips
      </a>
    );
  }

  return (
    <div className="adl-card">
      <div className="card-body">
        <h5 className="card-title">Advanced search tips</h5>
        <p className="card-text">
          <InterpretedSearchQuery initialParsedQuery={initialParsedQuery} />
        </p>
        <p className="card-text">
          If you enter <strong>magic ghoul party</strong> above, only adventures
          that contain all three terms will be found. If you want to search for
          adventures with either <strong>magic</strong>, <strong>ghoul</strong>,
          or <strong>party</strong>, try searching for{" "}
          <strong>magic OR ghoul OR party</strong>. Avoid using the search bar
          for things that are available as filters in the sidebar, since using
          the sidebar filters is more reliable. The search is slightly fuzzy,
          e.g., searching for <strong>ghouls</strong> will also result in
          adventures with <strong>ghoul</strong>.
          <br />
          You can also enclose search terms in quotes to search for an exact
          phrase: <strong>"ghoul party"</strong> only matches adventures where
          these terms occur in this order and directly after one another.
          <br />
          The <strong>AND</strong> operator takes precedence over the{" "}
          <strong>OR</strong> operator. For example,{" "}
          <strong>magic OR ghoul party</strong> is interpreted as{" "}
          <strong>magic OR (ghoul AND party)</strong>. You can use brackets to
          avoid that: <strong>(magic OR ghoul) party</strong> is interpreted as{" "}
          <strong>(magic OR ghoul) AND party</strong>.
        </p>
      </div>
    </div>
  );
}
