'use strict';
/**
 * Valid ID or NAME attributes for an HTML entity must begin with a letter ([A-Za-z]) and may be followed by any
 * number of letters, digits ([0-9]), hyphens ("-"), underscores ("_"), colons (":"), or periods (".").
 *
 * Invalid values can be found using the following regular expression: "[^A-Za-z0-9\-_:\.]"
 * NOTE: The above regular expression will also capture spaces, to make these mildly readable spaces will be
 * replaced with dashes after being collapsed to a maximum of 1 space.
 *
 * See: https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/id
 */
const invalidCharactersRegEx = /[^ A-Za-z0-9\-_:.]/g;
const condenseSpacesRegEx = /[\s]+/g;

const characterMap = require('./characterMap');

function createId(input) {
    // No need to do any processing if we didn't get anything to process.
    if (!input) {
        return '';
    }

    var returnValue = input.replace(invalidCharactersRegEx, (character) => {
        // Replace with the mapped character (if available), otherwise remove the character.
        return characterMap[character] || '';
    }).trim().toLowerCase();

    // Replace any space with a dash and condense any sets of multiple spaces into a single dash
    return returnValue.replace(condenseSpacesRegEx, '-');
}

module.exports = createId;