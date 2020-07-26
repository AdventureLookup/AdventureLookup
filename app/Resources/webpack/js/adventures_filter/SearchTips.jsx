import * as React from "react";

export function SearchTips() {
  const [expanded, setExpanded] = React.useState(false);

  if (!expanded) {
    return (
      <a
        id="search-tips-link"
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
      <div className="adl-card-body">
        <p className="adl-card-title">Advanced search tips</p>
        <p className="adl-card-text">
          If you enter <strong>magic ghoul party</strong> above, only adventures
          that contain all three terms will be found. If you want to search for
          adventures with either <strong>magic</strong>, <strong>ghoul</strong>,
          or <strong>party</strong>, try searching for{" "}
          <strong>magic OR ghoul OR party</strong>. Avoid using the search bar
          for things that are available as filters in the sidebar, since using
          the sidebar filters is more reliable. The search is slightly fuzzy,
          e.g., searching for <strong>ghouls</strong> will also result in
          adventures with <strong>ghoul</strong>.
        </p>
      </div>
    </div>
  );
}
