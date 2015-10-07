/**
 * This file is part of The Draconic Ephemeris Generator.
 *
 *  The Draconic Ephemeris Generator is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The Draconic Ephemeris Generator is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with The Draconic Ephemeris Generator.  If not, see <http://www.gnu.org/licenses/>.
 */
#include <stdio.h>
#include <time.h>
#include <string.h>
#include <stdbool.h>
#include "swephexp.h" 	/* this includes  "sweodef.h" */

int main( int argc, char *argv[] ) {

	/**
     * Variable Declarations
     */
    double  cday[2], // arrays to hold correct date values
            eday[2],
            current_day,
            end,
            h,
            x2[6], // holds planet position data
            degree, // holds the calculated nodal degree
            node_degree; // holds the nodal degree for calculations
    long    x; // holds output flag from swe_calc_ut
    int     i,
            r = 0,  // is the planet retrograde?
            p = 14,
            y, m, d, // to hold reverse date info
            planets[14] = {
                SE_TRUE_NODE,
                SE_SUN,
                SE_MOON,
                SE_MERCURY,
                SE_VENUS,
                SE_MARS,
                SE_JUNO,
                SE_CERES,
                SE_JUPITER,
                SE_SATURN,
                SE_CHIRON,
                SE_URANUS,
                SE_NEPTUNE,
                SE_PLUTO
            };
	char   pname[40],    // planet name
           serr[AS_MAXCH]; // to hold error messages
    FILE   *fp;
    
    // set up path to ephemeris files
	swe_set_ephe_path( "ephe" );
    
    // open the output file
    fp = fopen("dracdata.csv", "w");

    // get the beginning and end dates
    swe_utc_to_jd( 1950,  1,  1, 12, 0, 0, SE_GREG_CAL, cday, serr );
    swe_utc_to_jd( 2050, 12, 31, 12, 0, 0, SE_GREG_CAL, eday, serr );
    current_day = cday[ 1 ];
    end = eday[ 1 ];
        
    // loop through every day for 100 years
    while ( current_day <= end ) {
        
        // get the current day info
        swe_revjul( current_day, SE_GREG_CAL, &y, &m, &d, &h );

        // write the date to the file
        fprintf( fp, "{\"date\":\"%i-%02i-%02i\",\"planets\":{", y, m, d );

        // loop through all planets (at noon)
        for ( i = 0; i < p; i++ ) {
            // first, get the planet name
            swe_get_planet_name( planets[ i ], pname );

            // is it the north node?
            if ( SE_TRUE_NODE == planets[ i ] ) {
                // get the node's degree
                x = swe_calc_ut( current_day, planets[ i ], SEFLG_SPEED, x2, serr );
                node_degree = x2[ 0 ];
            } else {
                // is it the moon?
                if ( SE_MOON == planets[ i ] ) {
                    // get the moon's longitude at the previous midnight
                    x = swe_calc_ut( current_day - 0.5, planets[ i ], SEFLG_SPEED, x2, serr );
                    // adjust for the node
                    degree = node_degree > x2[ 0 ] ? x2[ 0 ] + 360.0 - node_degree : x2[ 0 ] - node_degree;
                    // add the longitude to the output
                    fprintf( fp, "\"Moon0\":{\"lon\":\"%010f\",\"ret\":\"0\"},", degree );
                }
                // get the planet info and write it to the file
                x = swe_calc_ut( current_day, planets[ i ], SEFLG_SPEED, x2, serr );
                // adjust for the node
                degree = node_degree > x2[ 0 ] ? x2[ 0 ] + 360.0 - node_degree : x2[ 0 ] - node_degree;
                r = x2[ 3 ] < 0 ? 1 : 0;
                fprintf( fp, "\"%s\":{\"lon\":\"%010f\",\"ret\":\"%i\"},", pname, degree, r );
            } 
        }
        // close out this row
        fprintf( fp, "\"node\":\"%010f\"}}\n", node_degree );

        // increment the day
        current_day += 1;
    }

    // close any files used
    swe_close();
    fclose(fp);
}