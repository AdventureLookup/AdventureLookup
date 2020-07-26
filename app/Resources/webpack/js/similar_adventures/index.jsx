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

const INITIAL_DATA = {
  adventures: [],
  terms: [],
};

const LOCALSTORAGE_KEY = "similar-adventures-selection";

function SimilarAdventuresRoot({ apiUrl }) {
  const [selectedField, setSelectedField] = React.useState(null);

  // Load initial selectedField from local storage.
  React.useEffect(() => {
    const localStorageSelectedField = window.localStorage.getItem(
      LOCALSTORAGE_KEY
    );
    if (Object.keys(availableFields).includes(localStorageSelectedField)) {
      setSelectedField(localStorageSelectedField);
    } else {
      setSelectedField("title/description");
    }
  }, []);

  // Persist change of selectedField to local storage.
  React.useEffect(() => {
    window.localStorage.setItem(LOCALSTORAGE_KEY, selectedField);
  }, [selectedField]);

  const [data, setData] = React.useState(INITIAL_DATA);
  const [ajaxState, setAjaxState] = React.useState("INITIAL");

  React.useEffect(() => {
    setAjaxState("INITIAL");
    if (selectedField === null) {
      setData(INITIAL_DATA);
      return;
    }

    $.getJSON(apiUrl, {
      fieldName: selectedField,
    })
      .done((data) => {
        setData(data);
        setAjaxState("DONE");
      })
      .fail(() => setAjaxState("FAILED"));
  }, [apiUrl, selectedField]);

  // Display debug values on demand.
  const debug = window.location.search === "?debug";

  const availableFields = {
    "title/description": "Title and Description",
    items: "Notable Items",
    bossMonsters: "Boss Monsters and Villains",
    commonMonsters: "Common Monsters",
  };

  return (
    <div className="adl-card">
      <div className="adl-card-body">
        <h3 className="adl-card-title">
          Similar and Related Adventures{" "}
          <span className="badge badge-warning">beta</span>
        </h3>
        <FieldSelector
          availableFields={availableFields}
          selectedField={selectedField}
          setSelectedField={(field) => setSelectedField(field)}
        />
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
              <p>
                We looked for adventures with the following keywords in{" "}
                {availableFields[selectedField]}, of which at least 25% must
                match:
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
            <div className="row">
              {data.adventures.map((adventure) => (
                <div className="col-12 col-lg-6 py-2" key={adventure.id}>
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
            {data.adventures.length === 0 && (
              <div className="alert alert-info">
                We couldn't find any similar adventures. Don't worry though: You
                can often find similar adventures on your own by searching for
                the most relevant keywords.
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}

function FieldSelector({ availableFields, selectedField, setSelectedField }) {
  return (
    <div className="d-flex mb-2">
      {Object.entries(availableFields).map(([name, label]) => (
        <div className="form-check form-check-inline" key={name}>
          <input
            id={`similar-adventures-checkbox-${name}`}
            className="form-check-input"
            type="radio"
            name="similar-adventures"
            checked={selectedField === name}
            onChange={() => setSelectedField(name)}
          />
          <label
            className="form-check-label"
            htmlFor={`similar-adventures-checkbox-${name}`}
          >
            {label}
          </label>
        </div>
      ))}
    </div>
  );
}
