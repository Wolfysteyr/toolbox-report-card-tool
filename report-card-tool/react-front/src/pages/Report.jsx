import { useState, useEffect } from "react";
import logo from "../assets/vtl-logo-1.svg";

const MONTH_NAMES = {
    1:  "Janvaris",
    2:  "Februaris",
    3:  "Marts",
    4:  "Aprilis",
    5:  "Maijs",
    6:  "Junijs",
    7:  "Julijs",
    8:  "Augusts",
    9:  "Septembris",
    10: "Oktobris",
    11: "Novembris",
    12: "Decembris",
};

export default function Report() {
    const [availableData, setAvailableData] = useState([]);

    const [selectedCar, setSelectedCar]     = useState("");
    const [selectedMonth, setSelectedMonth] = useState("");
    const [selectedYear, setSelectedYear]   = useState("");

    const [report, setReport] = useState(null);
    const [loading, setLoading] = useState(false);
    const [syncing, setSyncing] = useState(false);

    // Derived cascading options
    const availableYears  = [...new Set(availableData.map(r => r.year))].sort();
    const availableMonths = [...new Set(availableData.filter(r => r.year == selectedYear).map(r => parseInt(r.month)))].sort((a, b) => a - b);
    const availableCars   = [...new Set(availableData.filter(r => r.year == selectedYear && r.month == selectedMonth).map(r => r.carno))].sort();

    // Fetch available combinations on mount
    useEffect(() => {
        async function fetchAvailableData() {
            try {
                const res  = await fetch("/api/available-data");
                const data = await res.json();
                setAvailableData(data);

                const years  = [...new Set(data.map(r => r.year))].sort();
                const year   = years[0];
                const months = [...new Set(data.filter(r => r.year == year).map(r => parseInt(r.month)))].sort((a, b) => a - b);
                const month  = months[0];
                const cars   = [...new Set(data.filter(r => r.year == year && r.month == month).map(r => r.carno))].sort();

                setSelectedYear(year);
                setSelectedMonth(month);
                setSelectedCar(cars[0]);
            } catch (err) {
                console.error("Failed to fetch available data:", err);
            }
        }
        fetchAvailableData();
    }, []);

    // When year changes, cascade month and car
    useEffect(() => {
        if (!selectedYear || !availableData.length) return;
        const months = [...new Set(availableData.filter(r => r.year == selectedYear).map(r => parseInt(r.month)))].sort((a, b) => a - b);
        const month  = months[0];
        const cars   = [...new Set(availableData.filter(r => r.year == selectedYear && r.month == month).map(r => r.carno))].sort();
        setSelectedMonth(month);
        setSelectedCar(cars[0]);
    }, [selectedYear]);

    // When month changes, cascade car
    useEffect(() => {
        if (!selectedMonth || !availableData.length) return;
        const cars = [...new Set(availableData.filter(r => r.year == selectedYear && r.month == selectedMonth).map(r => r.carno))].sort();
        setSelectedCar(cars[0]);
    }, [selectedMonth]);

    // Fetch report whenever final selection is complete
    useEffect(() => {
        if (!selectedCar || !selectedMonth || !selectedYear) return;

        async function fetchReport() {
            setLoading(true);
            try {
                const res  = await fetch(`/api/fetch-data/${selectedCar}/${selectedMonth}/${selectedYear}`);
                const data = await res.json();
                setReport(data);
            } catch (err) {
                console.error("Failed to fetch report:", err);
                setReport(null);
            } finally {
                setLoading(false);
            }
        }
        fetchReport();
    }, [selectedCar, selectedMonth, selectedYear]);

    const handleSync = async () => {
        setSyncing(true);
        try {
            const res  = await fetch("/api/copy");
            const data = await res.json();
            alert(data.message);
        } catch (err) {
            console.error("Sync failed:", err);
            alert("Sinhronizacija neizdevas.");
        } finally {
            setSyncing(false);
        }
    };

    return (
        <>
            <div className="header">
                <img src={logo} alt="VTL logo" />
                <h1>Celazime</h1>
                <button onClick={handleSync} disabled={syncing}>
                    {syncing ? "Sinhronize..." : "Sinhronizet"}
                </button>
            </div>
            <div className="body">
                <div className="car-info">
                    <div>
                        <label htmlFor="car">Transportlīdzekļa marka:</label>
                        <input type="text" id="car" name="car" disabled value={report?.automarka ?? ""}/>

                        <label htmlFor="plate">Valsts reģ. numurs:</label>
                        <input type="text" id="plate" name="plate" disabled value={report?.carno ?? ""}/>

                        <label htmlFor="engine">Motora tilpums:</label>
                        <input type="text" id="engine" name="engine" disabled value={report?.motora_tilpums ?? ""}/>

                        <label htmlFor="fuelType">Degviela:</label>
                        <input type="text" id="fuelType" name="fuelType" disabled value={report?.product ?? ""}/>

                        <label htmlFor="fuelCap">Bakas tilpums:</label>
                        <input type="text" id="fuelCap" name="fuelCap" disabled value={report?.bakas_tilpums ?? ""}/>

                        <label htmlFor="driverName">Vadītajs:</label>
                        <input type="text" id="driverName" name="driverName" disabled value={report?.driver ?? ""}/>
                    </div>
                    <div>
                        <label htmlFor="reportFuelStart">Atlikums izbraucot (L):</label>
                        <input type="text" id="reportFuelStart" name="reportFuelStart" disabled value={report?.fuel_start ?? ""}/>

                        <label htmlFor="reportReceived">Saņemts (L):</label>
                        <input type="text" id="reportReceived" name="reportReceived" disabled value={report?.received ?? ""}/>

                        <label htmlFor="carFuelCons">L/100km norma:</label>
                        <input type="text" id="carFuelCons" name="carFuelCons" disabled value={report?.paterins ?? ""}/>

                        <label htmlFor="factualCarFuelCons">Faktiskais L/100km:</label>
                        <input type="text" id="factualCarFuelCons" name="factualCarFuelCons" disabled value={report?.factual_cons ?? ""}/>

                        <label htmlFor="reportUsedFuel">Izlietota degviela kopā (L):</label>
                        <input type="text" id="reportUsedFuel" name="reportUsedFuel" disabled value={report?.used ?? ""}/>

                        <label htmlFor="reportFuelEnd">Atlikums atgriežoties (L):</label>
                        <input type="text" id="reportFuelEnd" name="reportFuelEnd" disabled value={report?.fuel_end ?? ""}/>
                    </div>
                </div>

                <div className="car-selection">
                    <div className="selection-field">
                        <label htmlFor="carSelect">a/m valsts numurs</label>
                        <select id="carSelect" value={selectedCar} onChange={e => setSelectedCar(e.target.value)}>
                            {availableCars.map(car => (
                                <option key={car} value={car}>{car}</option>
                            ))}
                        </select>
                    </div>

                    <div className="selection-field">
                        <label htmlFor="year">gads</label>
                        <select id="year" value={selectedYear} onChange={e => setSelectedYear(e.target.value)}>
                            {availableYears.map(y => (
                                <option key={y} value={y}>{y}</option>
                            ))}
                        </select>
                    </div>

                    <div className="selection-field">
                        <label htmlFor="month">mēnesis</label>
                        <select id="month" value={selectedMonth} onChange={e => setSelectedMonth(e.target.value)}>
                            {availableMonths.map(m => (
                                <option key={m} value={m}>{MONTH_NAMES[m]}</option>
                            ))}
                        </select>
                    </div>
                </div>

                <div className="report-details">
                    <div className="period-details">
                        <div className="period-field">
                            <label htmlFor="startDate">Sākuma datums</label>
                            <input type="text" id="startDate" name="startDate" disabled value={report?.period_start ?? ""}/>
                        </div>
                        <div className="period-field">
                            <label htmlFor="endDate">Beigu datums</label>
                            <input type="text" id="endDate" name="endDate" disabled value={report?.period_end ?? ""}/>
                        </div>
                        <div className="period-field">
                            <label htmlFor="odoStart">Perioda sākumā</label>
                            <input type="text" id="odoStart" name="odoStart" disabled value={report?.odo_start ?? ""}/>
                        </div>
                        <div className="period-field">
                            <label htmlFor="odoEnd">Perioda beigās</label>
                            <input type="text" id="odoEnd" name="odoEnd" disabled value={report?.odo_end ?? ""}/>
                        </div>
                        <div className="period-field">
                            <label htmlFor="distance">Nobrauktie km</label>
                            <input type="text" id="distance" name="distance" disabled value={report?.distance ?? ""}/>
                        </div>
                    </div>
                    <br />
                    <div className="report-log">
                        <table>
                            <thead>
                                <tr>
                                    <th>Datums</th>
                                    <th>degviela</th>
                                    <th>daudzums(l)</th>
                                    <th>cena bez PVN</th>
                                    <th>vaditājs</th>
                                </tr>
                            </thead>
                            <tbody>
                                {(report?.log ?? []).map((entry, i) => (
                                    <tr key={i}>
                                        <td>{entry.date}</td>
                                        <td>{entry.product}</td>
                                        <td>{entry.amount}</td>
                                        <td>{entry.summa}</td>
                                        <td>{entry.driver}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="period-final">
                    <label htmlFor="finalDistance">Nobrauktie kilometri </label>
                    <input type="text" id="finalDistance" name="finalDistance" disabled value={report?.distance ?? ""}/>
                    <label htmlFor="finalFuelUsed">Patērētā degviela (litros)</label>
                    <input type="text" id="finalFuelUsed" name="finalFuelUsed" disabled value={report?.used ?? ""}/>
                    <label htmlFor="finalFuelCons">Degvielas patēriņs (l/100km)</label>
                    <input type="text" id="finalFuelCons" name="finalFuelCons" disabled value={report?.factual_cons ?? ""}/>
                    <label htmlFor="atbildigais">Atbildiga persona:</label>
                    <input type="text" id="atbildigais" name="atbildigais" disabled value={report?.atbildigais ?? ""} className="atbildigais"/>
                </div>
            </div>

            <footer>
                <p>SIS DOKUMENTS IR PARAKSTITS AR DROSU ELEKTRONISKO PARAKSTU UN SATUR LAIKA ZIMOGU</p>
            </footer>
        </>
    );
}