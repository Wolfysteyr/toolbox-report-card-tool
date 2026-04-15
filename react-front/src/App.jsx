import { BrowserRouter, Routes, Route } from "react-router-dom";
import Report from "./pages/Report";

import "./App.css";

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Report />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;