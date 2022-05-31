// For filling the table with data.
interface site_oefeningen {
	/**A positive integer.*/
	ID: number;
	/** The name of the exercise.*/
	name: string;
	/** The description of the exercise.*/
	description: string;
	/** Comma separated list of types.*/
	type: string|null;
	/** Comma separated list of musslegroups used in the exercise.*/
	spiergroepen: string|null;
	/** Estimated duration in seconds.*/
	duration: number|null;
	/** Estimated callori usage.*/
	calorien: number|null;
	/** Images */
	images: Array<string>|null;
	/** Videos */
	videos: Array<string>|null;
}
/**
 * A function to be called if the request fails.
 * The function receives three arguments:
 ** The jqXHR (in jQuery 1.4.x, XMLHttpRequest) object,
 ** a string describing the type of error that occurred
 ** and an optional exception object, if one occurred.
 * Possible values for the second argument (besides null) are "timeout", "error", "abort", and "parsererror".

 * When an HTTP error occurs, errorThrown receives the textual portion of the HTTP status, such as "Not Found" or "Internal Server Error". (in HTTP/2 it may instead be an empty string)
 * As of jQuery 1.5, the error setting can accept an array of functions.
 * Each function will be called in turn.
 * Note: This handler is not called for cross-domain script and cross-domain JSONP requests.
 * This is an Ajax Event.
 */
function errorHandling(_jqXHR: JQuery.jqXHR, textStatus: "timeout"|"error"|"abort"|"parsererror"|null, errorThrown?: string) {
	console.error(textStatus);
	if(errorThrown)
		console.error(errorThrown);
}
/**
 * A function to be called if the request succeeds.
 * The function gets passed three arguments:
 ** The data returned from the server, formatted according to the dataType parameter or the dataFilter callback function,
 ** if specified; a string describing the status;
 ** and the jqXHR (in jQuery 1.4.x, XMLHttpRequest) object.
 */
function successHandling(data: JSON|site_oefeningen[], _textStatus: string|null, jqXHR: JQuery.jqXHR) {
	if(!(data instanceof Array)) {
		errorHandling(jqXHR, "parsererror");
		return;
	}
	//get parameter from the URL of the page
	const queryString = window.location.search;
	const urlParams = new URLSearchParams(queryString);
	const page = urlParams.get('page')
	console.log(page);
		
	const container = document.getElementById("js-oefeningen");
	if(container == null) return;
	// Clear placeholders
	container.innerHTML = "";
	// For each row we add a article to the container.
	// The element is created filled with data and then added to its container.
	data.forEach(element => {
		const div = document.createElement('div');
		div.classList.add('col');
		const article = document.createElement('article');
		// The header element
		const header = document.createElement('h2');
		header.innerText = element.name;
		article.appendChild(header);
		// The atributes under the header but above the description. Each atribute has its own span.
		const atribs = document.createElement('p');
		atribs.classList.add('atributes');
		// A span for the estimated duration.
		const duration = document.createElement('span');
		duration.innerText = 
			(element.duration)?
				element.duration.toString()
				: "-";
		atribs.appendChild(duration);
		// A span for the estimated callories.
		const call = document.createElement('span');
		call.innerText =
			(element.calorien)?
				element.calorien.toString()
				: "-";
		atribs.appendChild(call);
		// A span for the type of exercise.
		const oType = document.createElement('span');
		oType.innerText =
			(element.type)?
				element.type
				: "-";
		atribs.appendChild(oType);
		article.appendChild(atribs);
		// The description.
		const desc = document.createElement('p');
		desc.classList.add('description');
		desc.innerText = element.description;
		article.appendChild(desc);
		// The musslegroups a exercise uses as tags under the description.
		const groups = document.createElement('p');
		groups.classList.add('tags');
		// Check if there are any musslegroups accociated with the exercise.
		if(element.spiergroepen) {
			// Converting the comma seperated list into an array and iterating over it.
			element.spiergroepen.split(',').forEach(element => {
				// Each musslegroup has it's own span.
				const attrib = document.createElement('span');
				attrib.innerText = "#" + element;
				groups.appendChild(attrib);
			});
		}
		article.appendChild(groups);
		// Lastly the image if one exists.
		const img = document.createElement('img');
		if(element.images && element.images[0])
			img.src = element.images[0];
		// An empty string into the alt attribute to mark it as decorative.
		img.setAttribute("alt", "");
		article.appendChild(img);
		// adds a button to allow oefening to be added to schema.
		if (page == 'schema')
		  {
				const btn = document.createElement('button');
				btn.textContent = 'Voeg toe aan schema';
				article.appendChild(btn);
		  }
		div.appendChild(article);
		container.appendChild(div);
	});
}
/**
 * Request the article to be added with data.
 * @param article Whether to get all exercises or only the favorites.
 */
function getData(article: null|"all"|"fav" = null): JQuery.jqXHR<any> {
	var settings: JQuery.AjaxSettings<any> = {
		accepts: {json:"application/json"},
		async: true,
		cache: true,
		dataType: "json",
		method: "GET",
		success: successHandling,
		error: errorHandling
	};
	switch(article) {
		case "fav":
			settings.url = "./php/getFavorites.php"; // Check what url to use
			break;
		default:
			settings.url = "./php/getOefeningen.php"; // Check what url to use
			break;
	}
	return $.ajax(settings);
}
window.addEventListener('load', function() {
	getData();
});