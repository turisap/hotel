/**
 * Created by HP on 14-Jun-17.
 */


// == NOTE ==
// This code uses ES6 promises. If you want to use this code in a browser
// that doesn't supporting promises natively, you'll have to include a polyfill.

gapi.analytics.ready(function() {

    /**
     * Authorize the user immediately if the user has already granted access.
     * If no access has been created, render an authorize button inside the
     * element with the ID "embed-api-auth-container".
     */
    gapi.analytics.auth.authorize({
        container: 'embed-api-auth-container',
        clientid: '474566537378-ajik84mbt7jlnf9n73bjin4h37cfk0op.apps.googleusercontent.com'
    });



    /**
     * Create a new ActiveUsers instance to be rendered inside of an
     * element with the id "active-users-container" and poll for changes every
     * five seconds.
     */
    var activeUsers = new gapi.analytics.ext.ActiveUsers({
        container: 'active-users-container',
        pollingInterval: 5
    });


    /**
     * Add CSS animation to visually show the when users come and go.
     */
    activeUsers.once('success', function() {
        var element = this.container.firstChild;
        var timeout;

        this.on('change', function(data) {
            var element = this.container.firstChild;
            var animationClass = data.delta > 0 ? 'is-increasing' : 'is-decreasing';
            element.className += (' ' + animationClass);

            clearTimeout(timeout);
            timeout = setTimeout(function() {
                element.className =
                    element.className.replace(/ is-(increasing|decreasing)/g, '');
            }, 3000);
        });
    });


    /**
     * Create a new ViewSelector2 instance to be rendered inside of an
     * element with the id "view-selector-container".
     */
    var viewSelector = new gapi.analytics.ext.ViewSelector2({
        container: 'view-selector-container',
    })
        .execute();


    /**
     * Update the activeUsers component, the Chartjs charts, and the dashboard
     * title whenever the user changes the view.
     */
    viewSelector.on('viewChange', function(data) {
        var title = document.getElementById('view-name');
        title.textContent = data.property.name + ' (' + data.view.name + ')';

        // Start tracking active users for this view.
        activeUsers.set(data).execute();

        // Render all the of charts for this view.
        renderWeekOverWeekChart(data.ids);

    });


    /**
     * Draw the a chart.js line chart with data from the specified view that
     * overlays session data for the current week over session data for the
     * previous week.
     */
    function renderWeekOverWeekChart(ids) {

        // Adjust `now` to experiment with different days, for testing only...
        var now = moment(); // .subtract(3, 'day');

        var thisWeek = query({
            'ids': ids,
            'dimensions': 'ga:date,ga:nthDay',
            'metrics': 'ga:sessions',
            'start-date': moment(now).subtract(1, 'day').day(0).format('YYYY-MM-DD'),
            'end-date': moment(now).format('YYYY-MM-DD')
        });

        var lastWeek = query({
            'ids': ids,
            'dimensions': 'ga:date,ga:nthDay',
            'metrics': 'ga:sessions',
            'start-date': moment(now).subtract(1, 'day').day(0).subtract(1, 'week')
                .format('YYYY-MM-DD'),
            'end-date': moment(now).subtract(1, 'day').day(6).subtract(1, 'week')
                .format('YYYY-MM-DD')
        });

        Promise.all([thisWeek, lastWeek]).then(function(results) {

            var data1 = results[0].rows.map(function(row) { return +row[2]; });
            var data2 = results[1].rows.map(function(row) { return +row[2]; });
            var labels = results[1].rows.map(function(row) { return +row[0]; });

            labels = labels.map(function(label) {
                return moment(label, 'YYYYMMDD').format('ddd');
            });

            var data = {
                labels : labels,
                datasets : [
                    {
                        label: 'Last Week',
                        fillColor : 'rgba(220,220,220,0.5)',
                        strokeColor : 'rgba(220,220,220,1)',
                        pointColor : 'rgba(220,220,220,1)',
                        pointStrokeColor : '#fff',
                        data : data2
                    },
                    {
                        label: 'This Week',
                        fillColor : 'rgba(151,187,205,0.5)',
                        strokeColor : 'rgba(151,187,205,1)',
                        pointColor : 'rgba(151,187,205,1)',
                        pointStrokeColor : '#fff',
                        data : data1
                    }
                ]
            };

            new Chart(makeCanvas('chart-1-container')).Line(data);
            generateLegend('legend-1-container', data.datasets);
        });
    }


    /**
     * Draw the a chart.js bar chart with data from the specified view that
     * overlays session data for the current year over session data for the
     * previous year, grouped by month.
     */



    /**
     * Extend the Embed APIs `gapi.analytics.report.Data` component to
     * return a promise the is fulfilled with the value returned by the API.
     * @param {Object} params The request parameters.
     * @return {Promise} A promise.
     */
    function query(params) {
        return new Promise(function(resolve, reject) {
            var data = new gapi.analytics.report.Data({query: params});
            data.once('success', function(response) { resolve(response); })
                .once('error', function(response) { reject(response); })
                .execute();
        });
    }


    /**
     * Create a new canvas inside the specified element. Set it to be the width
     * and height of its container.
     * @param {string} id The id attribute of the element to host the canvas.
     * @return {RenderingContext} The 2D canvas context.
     */
    function makeCanvas(id) {
        var container = document.getElementById(id);
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');

        container.innerHTML = '';
        canvas.width = container.offsetWidth;
        canvas.height = container.offsetHeight;
        container.appendChild(canvas);

        return ctx;
    }


    /**
     * Create a visual legend inside the specified element based off of a
     * Chart.js dataset.
     * @param {string} id The id attribute of the element to host the legend.
     * @param {Array.<Object>} items A list of labels and colors for the legend.
     */
    function generateLegend(id, items) {
        var legend = document.getElementById(id);
        legend.innerHTML = items.map(function(item) {
            var color = item.color || item.fillColor;
            var label = item.label;
            return '<li><i style="background:' + color + '"></i>' +
                escapeHtml(label) + '</li>';
        }).join('');
    }


    // Set some global Chart.js defaults.
    Chart.defaults.global.animationSteps = 60;
    Chart.defaults.global.animationEasing = 'easeInOutQuart';
    Chart.defaults.global.responsive = true;
    Chart.defaults.global.maintainAspectRatio = false;


    /**
     * Escapes a potentially unsafe HTML string.
     * @param {string} str An string that may contain HTML entities.
     * @return {string} The HTML-escaped string.
     */
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }



});