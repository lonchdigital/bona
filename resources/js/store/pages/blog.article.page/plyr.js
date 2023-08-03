import Plyr from "plyr";
import playSvg from '$img/plyr.svg';

export function init () {
    const players = Plyr.setup('.js-player',
        {
            iconUrl: playSvg,
        }
    );

}
