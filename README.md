# The Draconic Ephemeris Generator

This code was used to produce _The Draconic Ephemeris_, &copy; 2015 Morgan C. Benton. In accordance with the licensing agreement of the [Swiss Ephemeris](http://www.astro.com/swisseph/swephinfo_e.htm), which was used to produce the book, the source code is made available free of charge.

For those wishing to reproduce the text of the book, it is necessary to first obtain a copy of the Swiss Ephemeris program referred to above. Once downloaded, compiling and running `dracdata.c` will produce a file called `dracdata.csv`. This data file can then be read by `ephemeris.php`, which, when printed, will make use of `draconic.css` to produce a table-formatted version of the data.