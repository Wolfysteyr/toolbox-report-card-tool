import { useState, useEffect } from "react";

export default function Report() {

    // all of this is being copied from the provided PDFs and is subject to change once everything is discussed

    // state for pulled data for selected car and period
    const [data, setData] = useState(null); // not needed?

    // available cols
    // prev_date, periods, volume, prev_volume, summa, mileage, prev_mileage, mileage_consumption (literally just the driven km), product, carno, driver, bakas_tilpums, paterins, motora_tilpums, automarka, atbildigais
    // THIS IS SO CONFUSING

    // states for dropdown selections
    const [selectedCar, setSelectedCar] = useState("");
    const [selectedYear, setSelectedYear] = useState("");
    const [selectedMonth, setSelectedMonth] = useState("");

    const [availableCars, setAvailableCars] = useState([]); // for dropdown options
    const [availableMonths, setAvailableMonths] = useState([]); // for dropdown options
    const [availableYears, setAvailableYears] = useState([]); // for dropdown options

    // states for car info
    const [carMake, setCarMake] = useState(""); 
    const [carPlate, setCarPlate] = useState(""); 
    const [carEngineDisp, setCarEngineDisp] = useState(""); // why?
    const [carFuelType, setCarFuelType] = useState(""); 
    const [carFuelCap, setCarFuelCap] = useState("");
    const [carFuelCons, setCarFuelCons] = useState("");
    const [driverName, setDriverName] = useState("");
    
    // states for report details
    const [reportFuelStart, setReportFuelStart] = useState("");
    const [reportReceived, setReportReceived] = useState(""); // i guess this is the amount of fuel received during the period?
    const [reportFactualCons, setReportFactualCons] = useState("");
    const [reportUsedFuel, setReportUsedFuel] = useState("");
    const [reportFuelEnd, setReportFuelEnd] = useState("");

    // states for period details
    const [periodOdoStart, setPeriodOdoStart] = useState("");
    const [periodOdoEnd, setPeriodOdoEnd] = useState("");
    const [periodDistance, setPeriodDistance] = useState("");
    const [periodStartDate, setPeriodStartDate] = useState("");
    const [periodEndDate, setPeriodEndDate] = useState("");

    // states for report log
    const [reportLog, setReportLog] = useState([]); // this will be an array of objects with date, fuel type, amount, price, driver

    // effect to fetch data
    useEffect(() => {
        async function fetchAvailableData() {
            try {
                const response = await fetch("/api/available-data");
                const data = await response.json();
                setAvailableCars(data.cars);
                setAvailableMonths(data.months);
                setAvailableYears(data.years);
            } catch (err) {
                console.error("Failed to fetch available data: ", err);
            }
        }
        fetchAvailableData();
    }, []);
    
    // effect to fetch report data based on selections
    useEffect(() => {
        async function fetchReportData() {
            if (selectedCar && selectedMonth && selectedYear) {
                try {
                    const response = await fetch(`/api/fetch-data/${selectedCar}&${selectedMonth}&${selectedYear}`);
                    const data = await response.json();
                    // set all the states based on the fetched data
                    setCarMake(data.automarka);
                    setCarPlate(data.carno);
                    setCarEngineDisp(data.motora_tilpums);
                    setCarFuelType(data.product);
                    setCarFuelCap(data.bakas_tilpums);
                    setCarFuelCons(data.paterins);
                    setDriverName(data.driver);
                    setReportFuelStart(data.prev_volume);
                    setReportReceived(data.volume);
                    setReportFuelEnd(data.prev_volume + data.volume);


    const handleCopy = async () => {
        // placeholder, didnt even check if its correct, doesnt work obviously
        try{
            fetch("/api/copy")
            .then(response => response.json())
            .then(data => {
                const textToCopy = data.text; // assuming the API returns { text: "the text to copy" }
                navigator.clipboard.writeText(textToCopy)
                .then(() => {
                    alert("Teksts ir nokopēts uz starpliktuvi!");
                })
                .catch(err => {
                    console.error("Failed to copy: ", err);
                });
            })
            .catch(err => {
                console.error("Failed to fetch copy data: ", err);
            });
        }
            catch (err) {
                console.error("Failed to copy: ", err);
            }
    };

    return (
        <>
            <div className="header">    
                <img src="src\assets\vtl-logo-1.svg" />
                <h1>Ceļazīme</h1>
                <button onClick={handleCopy}>Kopēt</button>
            </div>
            <div className="body">
                <div className="car-info">
                    <div>
                        <label htmlFor="car">Transportlīdzekļa marka:</label>
                        <input type="text" id="car" name="car" disabled defaultValue={carMake}/>

                        <label htmlFor="plate">Valsts reg. numurs:</label>
                        <input type="text" id="plate" name="plate" disabled defaultValue={carPlate}/>

                        <label htmlFor="engine">Motora tilpums:</label>
                        <input type="text" id="engine" name="engine" disabled defaultValue={carEngineDisp}/>

                        <label htmlFor="fuelType">Degvielas :</label>
                        <input type="text" id="fuelType" name="fuelType" disabled defaultValue={carFuelType}/>

                        <label htmlFor="fuelCap">Bāka tilpums:</label>
                        <input type="text" id="fuelCap" name="fuelCap" disabled defaultValue={carFuelCap}/>

                        <label htmlFor="driverName">Vadītājs:</label>
                        <input type="text" id="driverName" name="driverName" disabled defaultValue={driverName}/>
                    </div>
                    {/* Report Details */}
                    <div>
                        <label htmlFor="reportFuelStart">Atlikums izbraucot:</label>
                        <input type="text" id="reportFuelStart" name="reportFuelStart" disabled defaultValue={reportFuelStart}/>

                        <label htmlFor="reportReceived">Saņemts:</label>
                        <input type="text" id="reportReceived" name="reportReceived" disabled defaultValue={reportReceived}/>

                        <label htmlFor="carFuelCons">Patēriņa norma uz 100 km:</label>
                        <input type="text" id="carFuelCons" name="carFuelCons" disabled defaultValue={carFuelCons}/>

                        <label htmlFor="factualCarFuelCons">Faktiskais patēriņš uz 100 km:</label>
                        <input type="text" id="factualCarFuelCons" name="factualCarFuelCons" disabled defaultValue={reportFactualCons}/>

                        <label htmlFor="reportUsedFuel">Izlietota degviela kopā:</label>
                        <input type="text" id="reportUsedFuel" name="reportUsedFuel" disabled defaultValue={reportUsedFuel}/>

                        <label htmlFor="reportFuelEnd">Atlikums atgriežoties:</label>
                        <input type="text" id="reportFuelEnd" name="reportFuelEnd" disabled defaultValue={reportFuelEnd}/>
                    </div>
                </div>

                <div className="car-selection">
                    <div className="selection-field">
                        <label htmlFor="carSelect">a/m valsts numurs</label>
                        <select id="carSelect" name="carSelect"> {/* placeholder */}
                            <option value="car1">Car 1</option>
                            <option value="car2">Car 2</option>
                            <option value="car3">Car 3</option>
                        </select>
                    </div>

                    <div className="selection-field">
                        <label htmlFor="year">gads</label>
                        <select id="year" name="year"> {/* placeholder */}
                            <option value="2026">2026</option>
                        </select>
                    </div>

                    <div className="selection-field">
                        <label htmlFor="month">mēnesis</label>
                        <select id="month" name="month"> {/* placeholder */}
                            <option value="january">Janvāris</option>
                            <option value="february">Februāris</option>
                            <option value="march">Marts</option>
                            <option value="april">Aprīlis</option>
                            <option value="may">Maijs</option>
                            <option value="june">Jūnijs</option>
                            <option value="july">Jūlijs</option>
                            <option value="august">Augusts</option>
                            <option value="september">Septembris</option>
                            <option value="october">Oktobris</option>
                            <option value="november">Novembris</option>
                            <option value="december">Decembris</option>
                        </select>
                    </div>
                </div>
                <div className="report-details">
                    <div className="period-details">
                        <div className="period-field">
                            <label htmlFor="startDate">Sākuma datums</label>
                            <input type="date" id="startDate" name="startDate" disabled defaultValue={periodStartDate}/>
                        </div>
                        <div className="period-field">
                            <label htmlFor="endDate">Beigu datums</label>
                            <input type="date" id="endDate" name="endDate" disabled defaultValue={periodEndDate}/>
                        </div>
                        <div className="period-field">
                            <label htmlFor="odoStart">Sākuma odometrs</label>
                            <input type="text" id="odoStart" name="odoStart" disabled defaultValue={periodOdoStart}/>
                        </div>
                        <div className="period-field">
                            <label htmlFor="odoEnd">Beigu odometrs</label>
                            <input type="text" id="odoEnd" name="odoEnd" disabled defaultValue={periodOdoEnd}/>
                        </div>
                        <div className="period-field">
                            <label htmlFor="distance">Nobraukums</label>
                            <input type="text" id="distance" name="distance" disabled defaultValue={periodDistance}/>
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
                                    <th>vadītājs</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr> {/* placeholder */}
                                    <td>2023-10-01</td>
                                    <td>DĪZELIS</td>
                                    <td>50</td>
                                    <td>75</td>
                                    <td>John Doe</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div className="period-final">
                    <label htmlFor="finalDistance">Nobraukums</label><br />
                    <input type="text" id="finalDistance" name="finalDistance" disabled/><br />
                    <label htmlFor="finalFuelCons">Patēriņš uz 100 km</label><br />
                    <input type="text" id="finalFuelCons" name="finalFuelCons" disabled/><br />
                    <label htmlFor="finalFuelUsed">Patērētā degviela (l)</label><br />
                    <input type="text" id="finalFuelUsed" name="finalFuelUsed" disabled/><br />
                </div>
            </div>

            <footer>
                <p>ŠIS DOKUMENTS IR PARAKSTĪTS AR DROŠU ELEKTRONISKO PARAKSTU UN SATUR LAIKA ZĪMOGU </p>
            </footer>
        </>
    )
}
