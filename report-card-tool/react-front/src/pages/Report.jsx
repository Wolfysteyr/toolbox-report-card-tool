import { useState, useEffect } from "react";

export default function Report() {

    // all of this is being copied from the provided PDFs and is subject to change once everything is discussed

    // state for pulled data for selected car and period
    // the entire table is copied over from the external database once (every day? hour? up to discussion ig), then revelant data is filtered out and displayed in the report
    const [data, setData] = useState(null); // not needed?

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

    // effect to fetch data from backend and do calculations for the report
    useEffect(() => {
        // fetch data for selected car and period
        // also fetch list of the cars and months that are in the database so as to not hardcode things

        // fetch here
        // fetch("api/report-data?car=selectedCar&period=selectedPeriod")
        // .then(response => response.json())
        // .then(data => {
        //     setData(data);
        //     // set all the states based on the fetched data
        //     setCarMake(data.carMake);
        //     setCarPlate(data.carPlate);
        //     setCarEngineDisp(data.carEngineDisp);
        //     setCarFuelType(data.carFuelType);
        //     setCarFuelCap(data.carFuelCap);
        //     setCarFuelCons(data.carFuelCons);
        //     setDriverName(data.driverName);
        //     
        //     dates and odo will be grabbed based on the first and last log entries for the period

        // CALCULATIONS \\
        //fuel used, need to sum up all the fuelings from log
        
        // avg fuel cons
        setReportFactualCons(periodDistance && reportUsedFuel ? (periodDistance / reportUsedFuel).toFixed(2) : "");  

        // period distance
        setPeriodDistance(periodOdoStart && periodOdoEnd ? (periodOdoEnd - periodOdoStart) : "");

    }, []); // dependencies will be the selected car and period
    return (
        <>
            <div className="header">    
                <img src="src\assets\vtl-logo-1.svg" />
                <h1>Ceļazīme</h1>
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
