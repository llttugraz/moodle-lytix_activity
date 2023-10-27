import Ajax from 'core/ajax';
import Templates from 'core/templates';
import Widget from 'lytix_helper/widget';
import PercentRounder from 'lytix_helper/percent_rounder';
import {makeLoggingFunction} from 'lytix_logs/logs';

const testData = {
    "Times": [
        {
            "Type": "Navigation",
            "Me": 0.82,
            "Others": 0.45
        }, {
            "Type": "Forum",
            "Me": 0,
            "Others": 0.06
        }, {
            "Type": "Grade",
            "Me": 0,
            "Others": 0
        }, {
            "Type": "Submission",
            "Me": 0,
            "Others": 0
        }, {
            "Type": "Resource",
            "Me": 0,
            "Others": 0.02
        }, {
            "Type": "Quiz",
            "Me": 0.15,
            "Others": 0.2
        }, {
            "Type": "Video",
            "Me": 0.03,
            "Others": 0.28
        }
    ],
    "ShowOthers": false
};

export const init = (contextid, courseid, userid) => {
    // const dataPromise = Widget.getData('local_lytix_lytix_activity_logs_get', {contextid, courseid, userid})
    const dataPromise = Promise.resolve(testData)
    .then(data => {
        // TODO Make sure this check makes sense.
        const
            times = data.Times,
            length = times.length;
        for (let i = 0; i < length; ++i) {
            if (times[i].Me > 0) {
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

        const renderBarChart = target => {
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
            if (context.length === 0) {
                context.push({
                    label: strings.noData,
                    percent: 100,
                });
            }
            // We have to wrap this with an object because the template needs a way to narrow down the context.
            // I realise that this explanation sound confusing, check out activity.mustache, that might help.
            return {data: context};
        };
        console.debug({
            me: renderBarChart('Me'),
            others: renderBarChart('Others'),
            showOthers: data.ShowOthers,
        });
        return Templates.render('lytix_activity/activity', {
            me: renderBarChart('Me'),
            others: renderBarChart('Others'),
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
    .catch(() => {
        widget.innerHTML = strings.error_text; // eslint-disable-line camelcase
    });
};
