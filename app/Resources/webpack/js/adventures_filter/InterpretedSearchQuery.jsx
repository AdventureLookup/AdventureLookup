import * as React from "react";

export const InterpretedSearchQuery = React.memo(
  function InterpretedSearchQuery({ initialParsedQuery }) {
    if (initialParsedQuery === null) {
      return null;
    }
    return (
      <>
        Your last search query has been interpreted as:{" "}
        <Element element={initialParsedQuery} isRoot={true} />
      </>
    );
  }
);

function Element({ element, isRoot }) {
  switch (element.type) {
    case "clause":
      return (
        <>
          {!isRoot && "( "}
          <Clause clause={element} />
          {!isRoot && " )"}
        </>
      );
    case "token":
      return <Token token={element} />;
    default:
      throw new Error(`Invalid element type "${element.type}".`);
  }
}

function Clause({ clause }) {
  return clause.children.map((child, i) => {
    return (
      <React.Fragment key={i}>
        <Element element={child} />
        {i < clause.children.length - 1 && <> {clause.operator} </>}
      </React.Fragment>
    );
  });
}

function Token({ token }) {
  return (
    <span className="badge badge-secondary">
      {token.kind === "phrase" && '"'}
      {token.content}
      {token.kind === "phrase" && '"'}
    </span>
  );
}
