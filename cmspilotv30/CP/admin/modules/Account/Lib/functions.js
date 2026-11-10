Util.createCPObject('cpm.account.lib');

cpm.account.lib = {
    getCurrentFieldIndexArr: function(fieldName){
        var currFldArr = fieldName.split('-');
        return {name: currFldArr[0], ind: currFldArr[1]};
    },

    getNextFieldIndexArr: function(fieldName){
        var currFldArr = fieldName.split('-');
        var index = parseInt(currFldArr[1]) + 1;
        var newFldName = currFldArr[0] + '-' + index;
        return {name: currFldArr[0], ind: index, newName: newFldName};
    }
}
