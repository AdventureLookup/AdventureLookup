import * as React from "react";
import * as ReactDOM from "react-dom";

(function () {
  if (!$("#similar-adventures-container").length) {
    return;
  }

  const root = document.getElementById("similar-adventures-container");
  const url = root.dataset.url;
  ReactDOM.render(<SimilarAdventuresRoot apiUrl={url} />, root);
})();

function SimilarAdventuresRoot({ apiUrl }) {
  const [data, setData] = React.useState({
    adventures: [],
    terms: [],
  });
  const [ajaxState, setAjaxState] = React.useState("INITIAL");
  React.useEffect(() => {
    $.getJSON(apiUrl, {})
      .done((data) => {
        setData(data);
        setAjaxState("DONE");
      })
      .fail(() => setAjaxState("FAILED"));
  }, [apiUrl]);

  // Display debug values on demand.
  const debug = window.location.search === "?debug";

  return (
    <div className="content-container mt-0">
      <h3 className="title">
        Similar and Related Adventures{" "}
        <span className="badge badge-warning">beta</span>
      </h3>
      {ajaxState === "INITIAL" && (
        <div className="alert alert-info">Fetching similar adventures...</div>
      )}
      {ajaxState === "FAILED" && (
        <div className="alert alert-danger">
          Something went wrong while trying to fetch similar adventures.
        </div>
      )}
      {ajaxState === "DONE" && (
        <>
          {data.terms.length > 0 && (
            <p className="mx-4">
              We looked for adventures with the following keywords in title and
              description, of which at least 25% must match:
              <br />
              {data.terms.map((term, i) => (
                <React.Fragment key={i}>
                  <span className="badge badge-secondary">
                    {term.term}{" "}
                    {debug && `(${Math.round(term["tf-idf"] * 10) / 10})`}
                  </span>{" "}
                </React.Fragment>
              ))}
            </p>
          )}
          <div className="mx-5">
            <div className="row">
              {data.adventures.map((adventure) => (
                <div className="col-12 col-lg-6 px-2 py-2" key={adventure.id}>
                  <div className="list-group">
                    <a
                      href={`/adventures/${adventure.slug}`}
                      className="list-group-item list-group-item-action"
                    >
                      <div className="d-flex w-100 justify-content-between">
                        <h5 className="mb-1">
                          {adventure.score > 100 && (
                            <span className="badge badge-primary">
                              good match
                            </span>
                          )}{" "}
                          {adventure.title}
                        </h5>
                        {debug && <small>{adventure.score}</small>}
                      </div>
                      <p className="mb-1 truncate-6">{adventure.description}</p>
                    </a>
                  </div>
                </div>
              ))}
            </div>
          </div>
          {data.adventures.length === 0 && (
            <div className="alert alert-info">
              We couldn't find any similar adventures. Don't worry though: You
              can often find similar adventures on your own by searching for the
              most relevant keywords.
            </div>
          )}
        </>
      )}
    </div>
  );
}
