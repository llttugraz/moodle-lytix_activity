import Ajax from 'core/ajax';
import {get_string as getString} from 'core/str';
import Templates from 'core/templates';
import Widget from 'lytix_helper/widget';
import PercentRounder from 'lytix_helper/percent_rounder';
import {makeLoggingFunction} from 'lytix_logs/logs';

const d3 = window.d3;

let log; // This will be the logging function.

const activity = {
    contextid: -1,
    courseid: -1,
    userid: -1,
    svg: null,
    margin: 0,
    width: 0,
    height: 0,
    x: 0,
    y: 0,
    z: 0,
    line: 0,
    focus: null,
    keys: null,
    formatValue: null,
    bisectDate: null,
    strings: null,
    sumUserString: '',
    sumAverageAllStrings: '',
    showOthers: true, // Also show all_*?

    svgcontainer: null,

    columns: [
        "average_all", "user_all",
        "all_core", "user_core",
        "all_forum", "user_forum",
        "all_grade", "user_grade",
        "all_submission", "user_submission",
        "all_resource", "user_resource",
        "all_quiz", "user_quiz",
        "all_bbb", "user_bbb",
        "date"
    ],

    data: [],

    renderGraphFail: function(ex) {
        // eslint-disable-next-line camelcase
        var text = (activity.strings.error_text += '<p>' + ex.message + '</p>');
        document.querySelector('#activitygraph_widget').innerHTML = text;
    },

    drawLoading: function() {
        var img = '<img src="../../../pix/i/loading.gif" ' +
            'alt="LoadingImage" style="width:48px;height:48px;">';
        var widget = document.getElementById('activitygraph_widget');
        getString('loading_msg', 'local_lytix').done(function(loadingMsg) {
            widget.innerHTML = img + ' ' + loadingMsg;
        });
        getString('sum_user', 'lytix_activity').done(function(sumUser) {
            activity.sumUserString = sumUser;
        });
        getString('sum_average', 'lytix_activity').done(function(sumAverage) {
            activity.sumAverageAllStrings = sumAverage;
        });
    },

    // ‘format’ is an object containing a divisor and a time unit (as returned by getFormatting).
    numberFormat: function(number, format = activity.currentFormatting) {
        return Math.round(number / format.divisor) + ' ' + format.unit;
    },

    // Convert seconds of a category to minutes or hours.
    reformat: function(input) {
        const
            keyAll = (input === "_all" ? "average" : "all") + input,
            keyUser = "user" + input;

        this.currentFormatting =
            this.showOthers && this.formatting[keyAll] >= this.formatting[keyUser] ?
            this.formatting[keyAll] :
            this.formatting[keyUser];

        const divisor = this.currentFormatting.divisor;
        return this.data.map(element => {
            return {
                [keyAll]: element[keyAll] / divisor,
                [keyUser]: element[keyUser] / divisor,
                date: element.date,
            };
        });
    },

    update: function(input) {

        var svgWidth = activity.svgcontainer.offsetWidth;
        var svgHeight = 410;

        const data = this.reformat(input);

        var loadingWidget = document.getElementById('activitygraph_widget');
        loadingWidget.innerHTML = '';

        var activityGraphWidget = d3.select("#activitygraph_widget");
        activityGraphWidget.selectAll('*').remove();

        activity.svg = activityGraphWidget.append("svg").attr("width", svgWidth).attr("height", svgHeight);

        activity.margin = {top: 15, right: 35, bottom: 15, left: 35};
        activity.width = activity.svg.attr("width") - activity.margin.left;
        activity.height = activity.svg.attr("height") - activity.margin.top - activity.margin.bottom;

        // Add label to y axis.
        activity.svg.append("text")
            .attr("text-anchor", "middle")
            .attr("y", 6)
            .attr("x", svgHeight / -2)
            .attr("dy", ".5em")
            .attr("transform", "rotate(-90)")
            .attr("font-size", 12)
            .text(this.currentFormatting.unit);

        activity.x = d3.scaleTime()
            .rangeRound([activity.margin.left, activity.width])
            .domain(d3.extent(data, function(d) {
                return d.date;
            }));

        activity.y = d3.scaleLinear()
            .rangeRound([activity.height - activity.margin.bottom, activity.margin.top]);

        activity.z = d3.scaleOrdinal(d3.schemeCategory10);

        var line = d3.line()
            .curve(d3.curveLinear)
            .x(function(d) {
                return activity.x(d.date);
            })
            .y(function(d) {
                return activity.y(d.degrees);
            });


        activity.svg.append("g")
            .attr("class", "x-axis")
            .attr("transform", "translate(0," + (activity.height - activity.margin.bottom) + ")")
            .call(d3.axisBottom(activity.x).tickFormat(d3.timeFormat("%b")));

        activity.svg.append("g")
            .attr("class", "y-axis")
            .attr("transform", "translate(" + activity.margin.left + ",0)");

        const focus = this.focus = activity.svg.append("g")
            .attr("class", "focus")
            .style("display", "none");

        focus.append("line").attr("class", "lineHover")
            .style("stroke", "#999")
            .attr("stroke-width", 1)
            .style("shape-rendering", "crispEdges")
            .style("opacity", 0.5)
            .attr("y1", -activity.height)
            .attr("y2", 0);

        focus.append("text").attr("class", "lineHoverDate")
            .attr("text-anchor", "middle")
            .attr("font-size", 12);

        activity.svg.append("rect")
            .attr("class", "overlay")
            .attr("x", activity.margin.left)
            .attr("width", activity.width - activity.margin.left)
            .attr("height", activity.height)
            .attr("fill", "none")
            .style("pointer-events", "all");


        var copy = activity.keys.filter(function(f) {
            return f.includes(input);
        });

        var cities = copy.map(function(id) {
            return {
                id: id,
                values: data.map(function(d) {
                    return {date: d.date, degrees: +d[id]};
                })
            };
        });
        var sumUserAll = 0;
        var sumAverageAll = 0;

        cities[1].values.forEach(function(d) {
            sumUserAll += d.degrees;
            return d;
        });

        // Undo the conversion from seconds to hours/minutes.
        sumUserAll *= this.formatting[cities[1].id].divisor;

        if (this.showOthers) {
            cities[0].values.forEach(function(d) {
                sumAverageAll += d.degrees;
                return d;
            });
            // Undo the conversion from seconds to hours/minutes.
            sumAverageAll *= this.formatting[cities[0].id].divisor;
        }

        // Delete others’ entries.
        if (!this.showOthers) {
            cities.shift();
            copy.shift();
        }

        var sumContainer = document.getElementById('activitygraph_sum');

        var innerHTML = '<div class="d-inline-block pr-4" style="font-family: sans-serif; font-size: 14px;">'
            + activity.sumUserString
            + activity.numberFormat(sumUserAll, this.getFormatting(sumUserAll));
        if (this.showOthers) {
            innerHTML += "/ "
            + activity.sumAverageAllStrings
            + activity.numberFormat(sumAverageAll, this.getFormatting(sumAverageAll));
        }
        innerHTML += '</div>';

        sumContainer.innerHTML = innerHTML;
        activity.y.domain([
            d3.min(cities, function(d) {
                return d3.min(d.values, function(c) {
                    return c.degrees;
                });
            }),
            d3.max(cities, function(d) {
                return d3.max(d.values, function(c) {
                    return c.degrees;
                });
            })
        ]).nice();


        activity.svg.selectAll(".y-axis")
            .call(d3.axisLeft(activity.y)
                .tickSize(-activity.width + activity.margin.left));

        var city = activity.svg.selectAll(".cities")
            .data(cities);

        city.exit().remove();

        city.enter().insert("g", ".focus").append("path")
            .attr("class", "line cities")
            .style("stroke", function(d) {
                return activity.z(d.id);
            })
            .merge(city)
            .attr("d", function(d) {
                return line(d.values);
            })
            .attr("fill", "none")
            .attr("stroke-width", "1.5px")
            .attr("opacity", 0.75);


        activity.tooltip(copy);
    },

    tooltip: function(copy) {
        const focus = this.focus;
        var labels = focus.selectAll(".lineHoverText")
            .data(copy);

        labels.enter().append("text")
            .attr("class", "lineHoverText")
            .style("fill", function(d) {
                return activity.z(d);
            })
            .attr("text-anchor", "start")
            .attr("font-size", 12)
            .attr("dy", function(_, i) {
                return 1 + i * 2 + "em";
            })
            .style("text-shadow", "-2px -2px 0 #fff, 2px -2px 0 #fff, -2px 2px 0 #fff, 2px 2px 0 #fff")
            .merge(labels);

        var circles = focus.selectAll(".hoverCircle")
            .data(copy);

        circles.enter().append("circle")
            .attr("class", "hoverCircle")
            .style("fill", function(d) {
                return activity.z(d);
            })
            .style("opacity", 0.75)
            .attr("r", 2.5)
            .merge(circles);

        activity.svg.selectAll(".overlay")
            .on("mouseover", function() {
                focus.style("display", null);
            })
            .on("mouseout", function() {
                focus.style("display", "none");
            })
            .on("mousemove", mousemove);

        /**
         * A callback function for an event listener.
         */
        function mousemove() {
            var x0 = activity.x.invert(d3.pointer(event)[0]),
                i = activity.bisectDate(activity.data, x0, 1),
                d0 = activity.data[i - 1];
            var d1;
            if (activity.data.length > 1) {
                d1 = activity.data[i];
            } else {
                d1 = d0;
            }
            var d = x0 - d0.date > d1.date - x0 ? d1 : d0;

            var formatDate = d3.timeFormat("%Y-%m-%d");

            focus.select(".lineHover")
                .attr("transform", "translate(" + activity.x(d.date) + "," + activity.height + ")");

            focus.select(".lineHoverDate")
                .attr("transform",
                    "translate(" + activity.x(d.date) + "," + (activity.height + activity.margin.bottom) + ")")
                .text(formatDate(d.date));

            focus.selectAll(".hoverCircle")
                .attr("cy", function(e) {
                    return activity.y(d[e]);
                })
                .attr("cx", activity.x(d.date));


            focus.selectAll(".lineHoverText")
                .attr("transform",
                    "translate(" + (activity.x(d.date)) + "," + activity.height / 2.5 + ")")
                .text(function(e) {
                        return activity.strings[e] +
                            " " + activity.numberFormat(d[e]);
                });

            if (activity.x(d.date) > (activity.width - activity.width / 4)) {
                focus.selectAll("text.lineHoverText")
                    .attr("text-anchor", "end")
                    .attr("dx", -10);
            } else {
                focus.selectAll("text.lineHoverText")
                    .attr("text-anchor", "start")
                    .attr("dx", 10);
            }
        }
    },

    chart: function(data) {

        activity.svgcontainer = document.getElementById('activitygraph_widget');

        activity.keys = activity.columns.slice(0);

        activity.bisectDate = d3.bisector(function(d) {
            return d.date;
        }).left;
        activity.formatValue = d3.format(",.0f");

        var parseTime = d3.timeParse("%Y%m%d");
        data.forEach(function(d) {
            d.date = parseTime(d.date);
            return d;
        });

        activity.update(document.getElementById('activitygraph_selectbox').value);
    },

    // Get divisor and time unit to convert seconds to either minutes or hours.
    getFormatting: function(seconds) {
        const
            spm = 60, // Seconds per minute.
            sph = 3600, // Seconds per hour.
            formatting = {
                unit: undefined,
                divisor: undefined,
            };
        if (seconds > spm && seconds < sph) {
            formatting.unit = activity.strings.m;
            formatting.divisor = spm;
        } else if (seconds > sph) {
            formatting.unit = activity.strings.h;
            formatting.divisor = sph;
        } else {
            formatting.unit = activity.strings.s;
            formatting.divisor = 1;
        }
        return formatting;
    },
};

export const init = async(contextid, courseid, userid) => {
    activity.contextid = contextid;
    activity.courseid = courseid;
    activity.userid = userid;

    activity.strings = await Widget.getStrings({
        lytix_activity: { // eslint-disable-line camelcase
            identical: [
                "average_all",
                "user_all",
                "all_core",
                "user_core",
                "all_forum",
                "user_forum",
                "all_grade",
                "user_grade",
                "all_submission",
                "user_submission",
                "all_resource",
                "user_resource",
                "all_quiz",
                "user_quiz",
                "all_bbb",
                "user_bbb",
                "error_text",
                "no_activities_found",
                "h",
                "m",
                "s"
            ],
        }
    });

    activity.drawLoading();

    const
        selectBox = document.getElementById('activitygraph_selectbox'),
        showOthersBox = document.getElementById('show-others'),
        chartChooser = document.getElementById('activity-chart-chooser'),
        barCharts = document.getElementById('activity-bar-charts'),
        barChartOthers = barCharts.querySelector('.others'),
        lineGraph = document.getElementById('activity-line-graph');

    // Perform ajax call.
    const dataPromise = Ajax.call([{
        methodname: 'local_lytix_lytix_activity_logs_get',
        args: {
            contextid: contextid,
            courseid: courseid,
            userid: userid,
        },
    }])[0]
    .then(function(response) {
        showOthersBox.checked = activity.showOthers = response.ShowOthers;
        if (activity.showOthers) {
            barChartOthers.classList.remove('d-none');
        }
        if (document.getElementById('show-line-graph').checked) {
            lineGraph.classList.remove('d-none');
            barCharts.classList.add('d-none');
        } // Per default the other chart is visible.

        activity.medianTimes = response.MedianTimes;
        if (response.data.length) {
            activity.data = response.data;
            activity.data.columns = activity.columns;
            const
                keys = activity.columns.slice(0, -1), // Exclude date.
                keyCount = keys.length;

            // Determine which category needs which unit of time.
            {
                activity.formatting = {};

                // First, figure out the highest amount of seconds per category.
                const maxTimes = {};
                for (let i = activity.data.length - 1; i >= 0; --i) {
                    const entry = activity.data[i];
                    for (let j = 0; j < keyCount; ++j) {
                        const key = keys[j];
                        if (entry[key] > (maxTimes[key] ?? 0)) {
                            maxTimes[key] = entry[key];
                        }
                    }
                }
                for (let i = 0; i < keyCount; ++i) {
                    const
                        key = keys[i],
                        time = maxTimes[key];
                    activity.formatting[key] = activity.getFormatting(time);
                }
            }

            activity.chart(activity.data);

            // XXX This has to be redeclared, otherwise its value mysteriously is an integer;
            // this is probably a side-effect of minimising.
            const selectBox = document.getElementById('activitygraph_selectbox');

            selectBox.addEventListener('change', function() {
                activity.update(this.value);
                // Slicing gets rid of leading underscore.
                log('SELECT', 'ACTIVITY', this.value.slice(1));
            });

            let resizeTimer;
            new ResizeObserver(() => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    activity.update(selectBox.value);
                }, 110);
            }).observe(lineGraph);
        } else {
            activity.renderGraphFail(activity.strings.no_activities_found);
        }
        return;
    }).fail(function(ex) {
        activity.renderGraphFail(ex);
    });

    // Render stacked bar charts.
    {
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
                    descriptionMe: 'description_me',
                    descriptionOthers: 'description_others',
                },
            },
        });

        log = makeLoggingFunction(userid, courseid, contextid, 'activity');

        Promise.all([stringPromise, dataPromise]).then(values => {
            const
                strings = values[0],
                times = activity.medianTimes,
                length = times.length,
                rounder = new PercentRounder();

            const renderBarChart = target => {
                const view = {
                    description: {text: strings['description' + target]},
                    data: [],
                };
                for (let i = 0; i < length; ++i) {
                    const
                        entry = times[i],
                        time = entry[target];
                    if (time <= 0) {
                        continue;
                    }
                    view.data.push({
                        activity: entry.Type.toLowerCase(),
                        label: strings[entry.Type],
                        percent: rounder.round(time * 100),
                    });
                }
                rounder.reset();
                if (view.data.length === 0) {
                    view.data.push({
                        label: strings.noData,
                        percent: 100,
                    });
                }
                return Templates.render('lytix_timeoverview/timeoverview', view)
                .then(html => {
                    barCharts.querySelector('.' + target.toLowerCase()).innerHTML = html;
                    return;
                });
            };
            return Promise.all([
                renderBarChart('Me'),
                renderBarChart('Others')
            ]);
        })
        .finally(() => {
            barCharts.classList.remove('loading');
        })
        .catch(() => {
            barCharts.innerHTML = activity.strings.error_text; // eslint-disable-line camelcase
        });
    }

    // Set up controls for choosing the type of chart and toggling others.
    {
        const lineGraphSelected = barCharts.classList.contains('d-none');
        chartChooser.addEventListener('change', () => {
            lineGraph.classList.toggle('d-none');
            barCharts.classList.toggle('d-none');
            log('SELECT', 'CHART', lineGraphSelected ? 'time per day' : 'aggregated');
        });

        showOthersBox.addEventListener('change', e => {
            const checked = e.target.checked;
            Ajax.call([{
                methodname: 'local_lytix_lytix_activity_toggle_others',
                args: {
                    userid: userid,
                    courseid: courseid,
                    contextid: contextid,
                    showothers: checked,
                },
            }]);
            activity.showOthers = checked;
            activity.update(selectBox.value);
            if (checked) {
                barChartOthers.classList.remove('d-none');
                log('SHOW', 'OTHERS');
            } else {
                barChartOthers.classList.add('d-none');
                log('HIDE', 'OTHERS');
            }
        });
    }
};
