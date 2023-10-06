/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
 
//get element
var ksElements = document.getElementsByClassName("ks-profile-detail-card");
//iterate elements
Array.prototype.forEach.call(ksElements, function(ksElement) {
  //check window width
  if(window.innerWidth < 568){
    if (ksElement.style.display === "none") {
      ksElement.style.display = "block";
    } else {
      ksElement.style.display = "none";
    }
  } else {
    if (ksElement.style.display === "none") {
      ksElement.style.display = "flex";
    } else {
      ksElement.style.display = "none";
    }
  }
});

//editor
function ksEditor(ksClassId1,ksClassId2,ksDisplayType,ksTextAreaId = null){
  //call function
  ksToggleEdit(ksClassId1,ksDisplayType,ksTextAreaId);
  ksToggleEdit(ksClassId2,ksDisplayType);
}

//toggle edit
function ksToggleEdit(ksClassID,ksDisplayType,ksTextAreaId = null){
  //get element
  var ksElement = document.getElementById(ksClassID);
  //check textarea
  if(ksTextAreaId != null){
      ksElement.innerHTML = tinyMCE.get(ksTextAreaId).getContent();
  }
  //check window width
  if(window.innerWidth < 568 && ksDisplayType == "flex"){
    if (ksElement.style.display === "none") {
      ksElement.style.display = "block";
    } else {
      ksElement.style.display = "none";
    }
  } else {
    if (ksElement.style.display === "none") {
      ksElement.style.display = ksDisplayType;
    } else {
      ksElement.style.display = "none";
    }
  }
}