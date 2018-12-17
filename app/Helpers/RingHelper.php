<?php

function getRingById($id) {
    switch ($id) {
        case 0:     return 'Leak';
        case 1:     return 'Fast Ring Skip Ahead';
        case 2:     return 'Fast Ring Active';
        case 3:     return 'Slow Ring';
        case 4:     return 'Preview Ring';
        case 5:     return 'Release Preview Ring';
        case 6:     return 'Semi-Annual Channel Targeted';
        case 7:     return 'Semi-Annual Channel Broad';
        case 8:     return 'Long-Term Suppprt Channel';
        default:    return;
    }
}

function getRingClassById($id) {
    switch ($id) {
        case 0:     return 'leak';
        case 1:     return 'skip';
        case 2:     return 'fast';
        case 3:     return 'slow';
        case 4:     return 'preview';
        case 5:     return 'release';
        case 6:     return 'targeted';
        case 7:     return 'broad';
        case 8:     return 'ltsc';
        default:    return;
    }
}