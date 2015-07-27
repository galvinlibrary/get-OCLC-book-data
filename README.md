# To generate a new file

(when leisure reading is refreshed)
set environment variable: OCLC_DEV_KEY

1. Install Node.js with NPM on a workstation
2. Clone this repository to the workstation: https://github.com/christinemcclure/leisure-process
3. Run npm install to create the package
4. Obtain a list of ISBNs from the leisure reading collection. Save the file as isbns-full.txt (create input options in the future) in the directory. (Verify isbnFile in index.js file is set correctly)
5. Run node index.js to generate the data file: leisureBooks.json
6. Copy the file to the front-end directory: currently: library.iit.edu\html\leisure


# Logic
##Preprocessing
1. Get output from Voyager client (all records with holdings in the leisure reading collection)
2. Save ISBN column only as text file

##Code

- [x] Open file (use only ISBN column of spreadsheet)
- [x] Read line
- [x] Split into array by space
- [x] Ensure alpha-numeric only (hyphens are added in display logic)
- [x] Check if ISBN is in processed array (skip duplicates)
- [x] Get OCLC record from WorldCat Search API by ISBN
- [x] Check for bad data
- [x] Convert to JSON
- [x] Write to file
- [ ] Add ISBN to processed array -- not needed
- [x] Get next line


# ISBN  examples

    1579549489 (alk. paper) 
    0345295706 : 
    9780670020836 
    159420229X 

