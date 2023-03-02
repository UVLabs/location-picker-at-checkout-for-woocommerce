/**
 * Add selectWoo to passed fields.
 *
 * @param {object} $ Instance of jQuery
 * @param {object} fields Array of fields to add selectWoo onto.
 */
export function attachSelectWooInstance($, fields) {
  for (const field of fields) {
    $(`#${field}`).selectWoo();
  }
}
