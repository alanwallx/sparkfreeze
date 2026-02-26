import { useState } from "react";
import { deleteSpark, Spark, SparkState } from "../api";


interface SparkListProps {
  sparks: Spark[];
  updateSparkState: (id: number, state: SparkState) => Promise<void>;
  deleteSpark: (id: number) => Promise<void>;
  loadSparks: () => void;
}

export const emojiMap = {
  [SparkState.Open]: "💥",
  [SparkState.Ignored]: "❄️",
  [SparkState.Searched]: "🔍",
  [SparkState.Finished]: "🔥",
};

export default function SparkList({
                                    sparks,
                                    updateSparkState,
                                    loadSparks,
                                  }: SparkListProps) {
  const [activeSparkId, setActiveSparkId] = useState<number | null>(null);

  return (
    <ul className={"sparks-list"} style={{ marginTop: "2rem" }}>
      {sparks.map((spark) => (
        <li
          key={spark.id}
          className={`spark-item ${activeSparkId === spark.id ? "active" : ""}`}
          onClick={() =>
            setActiveSparkId(activeSparkId === spark.id ? null : spark.id)
          }
          onMouseEnter={() =>
            setActiveSparkId(activeSparkId === spark.id ? null : spark.id)
          }
          onMouseLeave={() =>
            setActiveSparkId(null)
          }
          style={{ position: "relative" }}
        >
          <div className="spark-content">
            {emojiMap[spark.state]}&nbsp;
            <span
              className="spark-text"
              style={{
                textDecoration:
                  spark.state === SparkState.Ignored ? "line-through" : "none",
              }}
            >
              {spark.text}
            </span>
          </div>

          {activeSparkId === spark.id && (
            <div className="spark-buttons">
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  updateSparkState(
                    spark.id,
                    spark.state === SparkState.Ignored
                      ? SparkState.Open
                      : SparkState.Ignored
                  ).then(loadSparks);
                }}
              >
                {emojiMap[SparkState.Ignored]} {spark.state === SparkState.Ignored ? "Un-ignore" : "Ignore"}
              </button>
              <a
                className={"button"}
                href={`https://www.google.com/search?q=${encodeURIComponent(
                  spark.text
                )}`}
                target="_blank"
                rel="noreferrer"
                onClick={(e) => {
                  e.stopPropagation();
                  updateSparkState(spark.id, SparkState.Searched).then(loadSparks);
                }}
              >
                {emojiMap[SparkState.Searched]} Search
              </a>
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  updateSparkState(spark.id, SparkState.Finished).then(loadSparks);
                }}
              >
                {emojiMap[SparkState.Finished]} Finished
              </button>
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  deleteSpark(spark.id).then(loadSparks);
                }}
                style={{ color: "red" }}
              >
                ❌
              </button>
            </div>
          )}
        </li>
      ))}
    </ul>
  );
}
