import { Document, Page, View, Text, Image, StyleSheet, Font } from "@react-pdf/renderer";
import RobotoRegular from "../assets/fonts/Roboto-Regular.ttf";
import RobotoBold from "../assets/fonts/Roboto-Bold.ttf";

Font.register({
    family: "Roboto",
    fonts: [
        { src: RobotoRegular, fontWeight: "normal" },
        { src: RobotoBold,    fontWeight: "bold"   },
    ],
});

const S = StyleSheet.create({
    page: {
        padding: 32,
        fontSize: 9,
        fontFamily: "Roboto",
        color: "#000",
        backgroundColor: "#fff",
    },

    // Header
    header: {
        flexDirection: "row",
        justifyContent: "space-between",
        alignItems: "flex-start",
        marginBottom: 20,
    },
    logo: {
        width: 80,
    },
    title: {
        fontSize: 22,
        fontWeight: "bold",
        marginTop: 4,
    },
    carSelection: {
        flexDirection: "row",
        gap: 0,
    },
    selectionField: {
        alignItems: "flex-start",
        borderWidth: 1,
        borderColor: "#bbb",
        borderRightWidth: 0,
        paddingHorizontal: 8,
        paddingVertical: 4,
        minWidth: 80,
    },
    selectionFieldLast: {
        alignItems: "flex-start",
        borderWidth: 1,
        borderColor: "#bbb",
        paddingHorizontal: 8,
        paddingVertical: 4,
        minWidth: 80,
    },
    selectionLabel: {
        fontSize: 7,
        color: "#555",
        marginBottom: 3,
    },
    selectionValue: {
        fontWeight: "bold",
        fontSize: 9,
    },

    // Two-column info section
    infoSection: {
        flexDirection: "row",
        marginBottom: 16,
        gap: 24,
    },
    infoColumn: {
        width: "42%",
    },
    infoRow: {
        flexDirection: "row",
        alignItems: "flex-end",
        marginBottom: 6,
    },
    infoLabel: {
        width: "52%",
        fontSize: 9,
    },
    infoValue: {
        flex: 1,
        borderWidth: 1,
        borderColor: "#bbb",
        padding: 3,
        fontWeight: "bold",
        fontSize: 9,
        textAlign: "center",
    },

    // Period row
    periodRow: {
        flexDirection: "row",
        marginBottom: 16,
    },
    periodCell: {
        flex: 1,
        borderWidth: 1,
        borderRightWidth: 0,
        borderColor: "#bbb",
        padding: 5,
    },
    periodCellLast: {
        flex: 1,
        borderWidth: 1,
        borderColor: "#bbb",
        padding: 5,
    },
    periodCellLabel: {
        fontSize: 7,
        color: "#555",
        marginBottom: 4,
    },
    periodCellValue: {
        fontWeight: "bold",
        fontSize: 9,
    },

    // Bottom section: table left, summary right
    bottomSection: {
        flexDirection: "row",
        justifyContent: "space-between",
    },
    tableWrapper: {
        width: "55%",
    },

    // Refuel log table
    tableHeaderRow: {
        flexDirection: "row",
        borderBottomWidth: 1,
        borderColor: "#000",
        paddingBottom: 3,
        marginBottom: 2,
    },
    tableRow: {
        flexDirection: "row",
        paddingVertical: 3,
    },
    colDate:    { width: "22%", fontSize: 9 },
    colFuel:    { width: "28%", fontSize: 9 },
    colAmount:  { width: "16%", fontSize: 9 },
    colPrice:   { width: "14%", fontSize: 9 },
    colDriver:  { width: "20%", fontSize: 9 },
    headerText: { fontWeight: "bold", fontSize: 8, color: "#333" },

    // Summary box (right side)
    summaryWrapper: {
        width: "38%",
    },
    summaryRow: {
        flexDirection: "row",
        alignItems: "center",
        marginBottom: 5,
    },
    summaryLabel: {
        flex: 1,
        fontSize: 9,
    },
    summaryValue: {
        width: 90,
        borderWidth: 1,
        borderColor: "#bbb",
        padding: 3,
        textAlign: "right",
        fontWeight: "bold",
        fontSize: 9,
    },
    responsibleRow: {
        flexDirection: "row",
        alignItems: "center",
        marginTop: 16,
    },
    responsibleLabel: {
        fontSize: 9,
        marginRight: 8,
    },
    responsibleValue: {
        flex: 1,
        borderWidth: 1,
        borderColor: "#bbb",
        padding: 3,
        fontWeight: "bold",
        fontSize: 9,
        textAlign: "center",
    },

    // Footer
    footer: {
        position: "absolute",
        bottom: 20,
        left: 32,
        right: 32,
        fontSize: 7,
        color: "#777",
    },
});

const MONTH_NAMES = {
    1: "Janvāris",  2: "Februāris", 3: "Marts",
    4: "Aprīlis",   5: "Maijs",     6: "Jūnijs",
    7: "Jūlijs",    8: "Augusts",   9: "Septembris",
    10: "Oktobris", 11: "Novembris", 12: "Decembris",
};

export default function CarReportPDF({ reports, month, year, logoUrl }) {
    return (
        <Document>
            {reports.map((report, index) => (
                <Page key={index} size="A4" orientation="landscape" style={S.page} >

                    {/* Header */}
                    <View style={S.header}>
                        <Image style={S.logo} src={logoUrl} />
                        <Text style={S.title}>Ceļazīme</Text>
                        <View style={S.carSelection}>
                            <View style={S.selectionField}>
                                <Text style={S.selectionLabel}>a/m valsts numurs</Text>
                                <Text style={S.selectionValue}>{report.carno}</Text>
                            </View>
                            <View style={S.selectionField}>
                                <Text style={S.selectionLabel}>gads</Text>
                                <Text style={S.selectionValue}>{year}</Text>
                            </View>
                            <View style={S.selectionFieldLast}>
                                <Text style={S.selectionLabel}>mēnesis</Text>
                                <Text style={S.selectionValue}>{MONTH_NAMES[month]}</Text>
                            </View>
                        </View>
                    </View>

                    {/* Vehicle info + fuel balance */}
                    <View style={S.infoSection}>
                        <View style={S.infoColumn}>
                            <View style={S.infoRow}>
                                <Text style={S.infoLabel}>Transportlīdzekļa marka:</Text>
                                <Text style={S.infoValue}>{report.automarka}</Text>
                            </View>
                            <View style={S.infoRow}>
                                <Text style={S.infoLabel}>Valsts reģ. numurs:</Text>
                                <Text style={S.infoValue}>{report.carno}</Text>
                            </View>
                            <View style={S.infoRow}>
                                <Text style={S.infoLabel}>Motora tilpums:</Text>
                                <Text style={S.infoValue}>{report.motora_tilpums}</Text>
                            </View>
                            <View style={S.infoRow}>
                                <Text style={S.infoLabel}>Degviela:</Text>
                                <Text style={S.infoValue}>{report.product}</Text>
                            </View>
                            <View style={S.infoRow}>
                                <Text style={S.infoLabel}>Vadītājs:</Text>
                                <Text style={S.infoValue}>{report.driver}</Text>
                            </View>
                        </View>

                        <View style={S.infoColumn}>
                            <View style={S.infoRow}>
                                <Text style={S.infoLabel}>Atlikums izbraucot (L)</Text>
                                <Text style={S.infoValue}>{report.fuel_start}</Text>
                            </View>
                            <View style={S.infoRow}>
                                <Text style={S.infoLabel}>Saņemts (L)</Text>
                                <Text style={S.infoValue}>{report.received}</Text>
                            </View>
                            <View style={S.infoRow}>
                                <Text style={S.infoLabel}>L/100km norma</Text>
                                <Text style={S.infoValue}>{report.paterins}</Text>
                            </View>
                            <View style={S.infoRow}>
                                <Text style={S.infoLabel}>Faktiskais L/100km</Text>
                                <Text style={S.infoValue}>{report.factual_cons}</Text>
                            </View>
                            <View style={S.infoRow}>
                                <Text style={S.infoLabel}>Izlietota degviela kopa (L)</Text>
                                <Text style={S.infoValue}>{report.used}</Text>
                            </View>
                            <View style={S.infoRow}>
                                <Text style={S.infoLabel}>Atlikums atgriežoties (L)</Text>
                                <Text style={S.infoValue}>{report.fuel_end}</Text>
                            </View>
                        </View>
                    </View>

                    {/* Period row */}
                    <View style={S.periodRow}>
                        <View style={S.periodCell}>
                            <Text style={S.periodCellLabel}>Sākuma datums</Text>
                            <Text style={S.periodCellValue}>{report.period_start}</Text>
                        </View>
                        <View style={S.periodCell}>
                            <Text style={S.periodCellLabel}>Beigu datums</Text>
                            <Text style={S.periodCellValue}>{report.period_end}</Text>
                        </View>
                        <View style={S.periodCell}>
                            <Text style={S.periodCellLabel}>Perioda sākumā (km)</Text>
                            <Text style={S.periodCellValue}>{report.odo_start}</Text>
                        </View>
                        <View style={S.periodCell}>
                            <Text style={S.periodCellLabel}>Perioda beigās (km)</Text>
                            <Text style={S.periodCellValue}>{report.odo_end}</Text>
                        </View>
                        <View style={S.periodCellLast}>
                            <Text style={S.periodCellLabel}>Nobrauktie km</Text>
                            <Text style={S.periodCellValue}>{report.distance}</Text>
                        </View>
                    </View>

                    {/* Bottom: table + summary side by side */}
                    <View style={S.bottomSection}>

                        {/* Refuel log table */}
                        <View style={S.tableWrapper}>
                            <View style={S.tableHeaderRow}>
                                <Text style={[S.colDate,   S.headerText]}>datums</Text>
                                <Text style={[S.colFuel,   S.headerText]}>degviela</Text>
                                <Text style={[S.colAmount, S.headerText]}>daudzums (L)</Text>
                                <Text style={[S.colPrice,  S.headerText]}>cena bez PVN</Text>
                                <Text style={[S.colDriver, S.headerText]}>vadītājs</Text>
                            </View>
                            {(report.log ?? []).map((entry, i) => (
                                <View key={i} style={S.tableRow}>
                                    <Text style={S.colDate}>{entry.date}</Text>
                                    <Text style={S.colFuel}>{entry.product}</Text>
                                    <Text style={S.colAmount}>{entry.amount}</Text>
                                    <Text style={S.colPrice}>{entry.summa}</Text>
                                    <Text style={S.colDriver}>{entry.driver}</Text>
                                </View>
                            ))}
                        </View>

                        {/* Summary */}
                        <View style={S.summaryWrapper}>
                            <View style={S.summaryRow}>
                                <Text style={S.summaryLabel}>Nobrauktie kilometri</Text>
                                <Text style={S.summaryValue}>{report.distance}</Text>
                            </View>
                            <View style={S.summaryRow}>
                                <Text style={S.summaryLabel}>Patērētā degviela (litros)</Text>
                                <Text style={S.summaryValue}>{report.used}</Text>
                            </View>
                            <View style={S.summaryRow}>
                                <Text style={S.summaryLabel}>Degviela patēriņš (l/100km)</Text>
                                <Text style={S.summaryValue}>{report.factual_cons}</Text>
                            </View>
                            <View style={S.responsibleRow}>
                                <Text style={S.responsibleLabel}>Atbildīga persona:</Text>
                                <Text style={S.responsibleValue}>{report.atbildigais}</Text>
                            </View>
                        </View>

                    </View>

                    {/* Footer */}
                    <View style={S.footer}>
                        <Text>ŠIS DOKUMENTS IR PARAKSTĪTS AR DROŠU ELEKTRONISKO PARAKSTU UN SATUR LAIKA ZĪMOGU</Text>
                    </View>

                </Page>
            ))}
        </Document>
    );
}