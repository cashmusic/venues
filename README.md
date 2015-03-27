# Bobo Venue Database
The goal of this project is to create an open data portal for music venue information. This means
we'll serve human and machine readable information free for all to use and distribute. All data 
will be available under a Creative Commons CC0 declaration, making it public domain.

To start, we'll offer HTML and JSON endpoints for search and detailed venue data.

This simple interface will allow other services to leverage open venue data in their own applications,
starting with the CASH Music platform. 


## Routes
We'll use a simple URL scheme:

Search endpoint:
/venues/term (JSON)
/venues/term.html (HTML)

Detail data endpoint:
/venue/identifier (JSON)
/venue/identifier.html (HTML)


## Data format
Search results:
```JSON
[
	{
		"identifier":"04ft9",
		"name":"The Echo",
		"city":"Los Angeles",
		"country":"USA"
	},
	{
		"identifier":"316y8",
		"name":"Echoplex",
		"city":"Los Angeles",
		"country":"USA"
	}
]
```

Venue data:
```JSON
{
	"identifier":"04ft9",
	"name":"The Echo",
	"type":"venue",
	"address1":"1822 Sunset Blvd",
	"address2":"",
	"city":"Los Angeles",
	"region":"California",
	"country":"USA",
	"postalcode":"90026",
	"latitude":34.077729,
	"longitude":-118.260108,
	"url":"http://www.theecho.com/",
	"phone":"(213) 413-8200",
	"email":"",
	"capacity:350,
	"creation_date":1427481092,
	"modification_date":1427483107
}
```