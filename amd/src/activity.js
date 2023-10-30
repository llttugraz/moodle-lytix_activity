import Ajax from 'core/ajax';
import Templates from 'core/templates';
import Widget from 'lytix_helper/widget';
import PercentRounder from 'lytix_helper/percent_rounder';
import {makeLoggingFunction} from 'lytix_logs/logs';

export const init = (contextid, courseid, userid) => {
    const dataPromise = Widget.getData('local_lytix_lytix_activity_logs_get', {contextid, courseid, userid})
    .then(data => {
        const
            times = data.Times,
            length = times.length;
        for (let i = 0; i < length; ++i) {
            if (times[i].Me > 0 || times[i].Others > 0) {
                return data;
            }
        }
        throw new Widget.NoDataError();
    });

    const stringPromise = Widget.getStrings({
        lytix_activity: { // eslint-disable-line camelcase
            differing: {
                Navigation: 'core',
                Quiz: 'quiz',
                Video: 'video',
                Grade: 'grade',
                Forum: 'forum',
                Resource: 'resource',
                Submission: 'submission',
                noData: 'nodata',
            },
        },
    });

    const
        widget = document.getElementById('activity'),
        log = makeLoggingFunction(userid, courseid, contextid, 'activity');

    Promise.all([stringPromise, dataPromise]).then(values => {
        const
            strings = values[0],
            data = values[1],
            times = data.Times,
            length = times.length,
            rounder = new PercentRounder();

        const generateChartContext = target => {
            const context = [];
            for (let i = 0; i < length; ++i) {
                const
                    entry = times[i],
                    time = entry[target];
                if (time <= 0) {
                    continue;
                }
                context.push({
                    activity: entry.Type.toLowerCase(),
                    label: strings[entry.Type],
                    percent: rounder.round(time * 100),
                });
            }
            rounder.reset();

            // We have to wrap this with an object because the template needs a way to narrow down the context.
            // I realise that this explanation sound confusing, check out activity.mustache, that might help.
            return context.length > 0 ? {data: context} : false;
        };
        return Templates.render('lytix_activity/activity', {
            me: generateChartContext('Me'),
            others: generateChartContext('Others'),
            showOthers: data.ShowOthers,
        });
    })
    .then(html => {
        widget.querySelector('.content').innerHTML = html;

        // Set up controls for toggling others.
        const barChartOthers = widget.querySelector('.others');
        document.getElementById('show-others').addEventListener('change', e => {
            const checked = e.target.checked;
            Ajax.call([{
                methodname: 'local_lytix_lytix_activity_toggle_others',
                args: {userid, courseid, contextid, showothers: checked},
            }]);
            if (checked) {
                barChartOthers.classList.remove('d-none');
                log('SHOW', 'OTHERS');
            } else {
                barChartOthers.classList.add('d-none');
                log('HIDE', 'OTHERS');
            }
        });
        return;
    })
    .finally(() => {
        widget.classList.remove('loading');
    })
    .catch(error => Widget.handleError(error, 'activity'));
};
