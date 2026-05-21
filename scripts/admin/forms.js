function getUnix(dateString) {
	const date = new Date(dateString);
	return isNaN(date) ? null : Math.floor(date.getTime() / 1000);
}

$(document).ready(async function() {
	addPage("Forms", async (page)=>{
    page.append($("<center>").append(("<p>Loading Please Wait...</p>")))
		let rawData = (await post("/ajax/admin.php", {
			function: 3,
			startTime: getUnix("01-01-2026 01:00:00"),
			endTime: getUnix("01-01-2027 01:00:00") - 1
		}));
    page.empty();

    console.log("Forms Page Data:", rawData);
    if(rawData.data == undefined) {
      displayError(JSON.stringify(rawData));
      return;
    }
    let data = rawData.data;

    for(let row of data) {
      // Trim days to match the format of M Tu W Th F
      let pickupDays = "";
      if(row.pickupMon == 1) pickupDays += "M ";
      if(row.pickupTue == 1) pickupDays += "Tu ";
      if(row.pickupWed == 1) pickupDays += "W ";
      if(row.pickupThu == 1) pickupDays += "Th ";
      if(row.pickupFri == 1) pickupDays += "F ";
      row.pickupDays = pickupDays;

      delete row.pickupMon;
      delete row.pickupTue;
      delete row.pickupWed;
      delete row.pickupThu;
      delete row.pickupFri;

      // Make individuals show up on the same row
      let individualStr = "";
      if('individuals' in row) {
        let tempStr = "";
        for(let individual of row.individuals) {
          tempStr += individual.individualName + "<br>";
        }
        individualStr = tempStr;
      } else {
        individualStr = "Data not found";
      }
      row.individuals = individualStr;
    }
    //

		let tableDiv = mktable(data, {
			headerNames: tableHeaderNames,
			triggers: tableTriggers,
      onRowClick: inspectForm,
      rowTrigger: function(row, rowData) {
        if(rowData.isEnabled != 1) {
          row.css({"background-color":"#B9B8B5"})
        }
      },
      ignore: ["isEnabled"],
      onContext: {
        "Delete": async (row)=>{
          // Delete form
          let obj = await post("/ajax/admin.php", {
            function: 16,
            formID: row.formID
          });
          if(obj.code >= 100 && obj.code < 200) {
            location.reload();
          }
        }
      },
      showExport: true
		});

    console.log(tableDiv)
		page.append(tableDiv);
	});
});