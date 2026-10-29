import { scoreAssessment } from "../src/shared/engine.js";
import { createCard, scanView, viewFromResult } from "../src/content/view.js";

const board = document.querySelector("#board");
const samples = [
  scanView("reuters.com", "Central bank holds rates as inflation cools further this quarter"),
  viewFromResult(scoreAssessment({
    url: "https://www.reuters.com/world/central-bank-holds-rates",
    anchorText: "Central bank holds rates as inflation cools further this quarter",
  })),
  viewFromResult(scoreAssessment({
    url: "https://tass.ru/ekonomika/1234567",
    anchorText: "По данным Минфина, дефицит бюджета за квартал составил 1,2 триллиона рублей",
  })),
  viewFromResult(scoreAssessment({
    url: "https://worldnewsdailyreport.com/city-opens-library",
    anchorText: "СРОЧНО!!! Врачи молчат: чудо-средство, вы не поверите!!!",
  })),
  viewFromResult(scoreAssessment({
    url: "https://panorama.pub/politics/monday",
    anchorText: "Госдума предложила отменить понедельник насовсем",
  })),
];

for (const sample of samples) {
  const slot = document.createElement("div");
  slot.className = "slot";
  const card = createCard();
  card.setState(sample);
  card.shell.classList.add("is-in");
  slot.append(card.shell);
  board.append(slot);
}
