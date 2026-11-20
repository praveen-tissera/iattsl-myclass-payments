<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$chart_branches = array();
$chart_series = array();
foreach ($report['branch_totals'] as $branch_total) {
    $branch_name = $branch_total->branch;
    $chart_branches[] = array(
        'label' => $branch_name,
        'income' => (float) $branch_total->income
    );
    $values = array();
    foreach ($report['timeline'] as $entry) {
        $values[] = isset($entry['branches'][$branch_name]) ? (float) $entry['branches'][$branch_name] : 0;
    }
    $chart_series[] = array(
        'label' => $branch_name,
        'values' => $values
    );
}

$chart_data = array(
    'timeline' => $report['timeline'],
    'branches' => $chart_branches,
    'series' => $chart_series
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('css/institute-inner.css'); ?>">
    <title>Income Analysis Report</title>
    <style>
        .income-chart-card { min-height: 100%; }
        .income-chart { display: block; width: 100%; height: auto; overflow: visible; }
        .income-chart text { fill: #526174; font-size: 12px; }
        .income-chart .grid-line { stroke: #e4e9ef; stroke-width: 1; }
        .income-chart .axis-line { stroke: #aab5c2; stroke-width: 1; }
        .income-chart .income-line { fill: none; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; }
        .income-chart .income-point { fill: #fff; stroke-width: 2; }
        .income-legend { display: flex; flex-wrap: wrap; gap: .5rem 1rem; padding: 0; margin: 1rem 0 0; list-style: none; }
        .income-legend li { display: flex; align-items: center; font-size: .875rem; }
        .income-legend-swatch { display: inline-block; width: .75rem; height: .75rem; margin-right: .4rem; border-radius: 2px; }
        .income-empty-chart { color: #6c757d; padding: 4rem 1rem; text-align: center; }
    </style>
</head>
<body class="institute-inner">
    <?php
    if ($this->session->userdata('user_role') === 'administrator') {
        $this->load->view('includesui/menu_admin');
    } else {
        $this->load->view('includesui/menu_cordinator');
    }
    ?>
    <main class="container-fluid mt-4">
        <h1 class="mb-4">Income Analysis Report</h1>
        <?php echo form_open('admin_income_analysis', array('method' => 'get', 'class' => 'card card-body mb-4')); ?>
            <input type="hidden" name="filter_submitted" value="1">
            <div class="form-row align-items-end">
                <div class="form-group col-md-4">
                    <label for="session_ids">Academic years</label>
                    <select class="form-control" id="session_ids" name="session_ids[]" multiple size="5" required>
                        <?php foreach ($academic_years as $academic_year): ?>
                            <option value="<?php echo (int) $academic_year->ID; ?>"<?php echo in_array((string) $academic_year->ID, $selected_years, TRUE) ? ' selected' : ''; ?>>
                                <?php echo html_escape($academic_year->label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm btn-link px-0 select-all" type="button" data-target="session_ids">Select all years</button>
                </div>
                <div class="form-group col-md-4">
                    <label for="branches">Branches</label>
                    <select class="form-control" id="branches" name="branches[]" multiple size="5">
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo html_escape($branch); ?>"<?php echo in_array($branch, $selected_branches, TRUE) ? ' selected' : ''; ?>>
                                <?php echo html_escape($branch); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm btn-link px-0 select-all" type="button" data-target="branches">Select all branches</button>
                    <small class="form-text text-muted">Leave unselected to include every branch.</small>
                </div>
                <div class="form-group col-md-4">
                    <label for="grade_ids">Grades</label>
                    <select class="form-control" id="grade_ids" name="grade_ids[]" multiple size="5">
                        <?php foreach ($grades as $grade): ?>
                            <option value="<?php echo (int) $grade->ID; ?>"<?php echo in_array((string) $grade->ID, $selected_grades, TRUE) ? ' selected' : ''; ?>>
                                <?php echo html_escape($grade->label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm btn-link px-0 select-all" type="button" data-target="grade_ids">Select all grades</button>
                    <small class="form-text text-muted">Leave unselected to include every grade.</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="subject_ids">Subjects</label>
                    <select class="form-control" id="subject_ids" name="subject_ids[]" multiple size="5">
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo (int) $subject->ID; ?>"<?php echo in_array((string) $subject->ID, $selected_subjects, TRUE) ? ' selected' : ''; ?>>
                                <?php echo html_escape($subject->grade_label . ' - ' . $subject->label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm btn-link px-0 select-all" type="button" data-target="subject_ids">Select all subjects</button>
                    <small class="form-text text-muted">Leave unselected to include every subject.</small>
                </div>
                <div class="form-group col-md-3">
                    <label for="period">Income period</label>
                    <select class="form-control" id="period" name="period">
                        <option value="monthly"<?php echo $period === 'monthly' ? ' selected' : ''; ?>>Monthly</option>
                        <option value="yearly"<?php echo $period === 'yearly' ? ' selected' : ''; ?>>Yearly</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <button type="submit" class="btn btn-primary btn-block">Generate report</button>
                </div>
            </div>
            <small class="text-muted">Use Ctrl (Windows) or Command (Mac) to select multiple options. Income is grouped by the payment date.</small>
        <?php echo form_close(); ?>

        <?php if ($has_filters && empty($selected_years)): ?>
            <div class="alert alert-warning">Select at least one academic year to generate the report.</div>
        <?php elseif ($has_filters): ?>
            <div class="alert alert-info">
                <strong>Total income:</strong> <?php echo number_format($report['total_income'], 2); ?>
            </div>
            <?php if (empty($report['timeline'])): ?>
                <div class="alert alert-warning">No income matched the selected filters.</div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <section class="card income-chart-card">
                            <div class="card-body">
                                <h2 class="h5">Income trend (<?php echo html_escape(ucfirst($period)); ?>)</h2>
                                <svg id="income-line-chart" class="income-chart" viewBox="0 0 900 340" role="img" aria-label="Smooth line chart of income by <?php echo html_escape($period); ?>"></svg>
                                <ul id="income-line-legend" class="income-legend"></ul>
                            </div>
                        </section>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <section class="card income-chart-card">
                            <div class="card-body">
                                <h2 class="h5">Income by branch</h2>
                                <svg id="income-pie-chart" class="income-chart" viewBox="0 0 420 340" role="img" aria-label="Pie chart showing income by branch"></svg>
                                <ul id="income-pie-legend" class="income-legend"></ul>
                            </div>
                        </section>
                    </div>
                    <div class="col-12 mb-4">
                        <section class="card">
                            <div class="card-body">
                                <h2 class="h5">Income by <?php echo $period === 'monthly' ? 'month' : 'year'; ?></h2>
                                <svg id="income-column-chart" class="income-chart" viewBox="0 0 900 340" role="img" aria-label="Column chart showing income by <?php echo html_escape($period); ?>"></svg>
                                <ul id="income-column-legend" class="income-legend"></ul>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="h5">Income details</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th><?php echo html_escape(ucfirst($period)); ?></th>
                                        <?php foreach ($chart_branches as $branch): ?>
                                            <th class="text-right"><?php echo html_escape($branch['label']); ?></th>
                                        <?php endforeach; ?>
                                        <th class="text-right">Total income</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($report['timeline'] as $entry): ?>
                                        <tr>
                                            <td><?php echo html_escape($entry['label']); ?></td>
                                            <?php $period_total = 0; ?>
                                            <?php foreach ($chart_branches as $branch): ?>
                                                <?php $branch_income = isset($entry['branches'][$branch['label']]) ? $entry['branches'][$branch['label']] : 0; ?>
                                                <?php $period_total += $branch_income; ?>
                                                <td class="text-right"><?php echo number_format($branch_income, 2); ?></td>
                                            <?php endforeach; ?>
                                            <td class="text-right"><?php echo number_format($period_total, 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>
    <script>
        (function () {
            var data = <?php echo json_encode($chart_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            var colors = ['#2867c7', '#f28e2b', '#59a14f', '#e15759', '#af7aa1', '#76b7b2', '#edc949', '#ff9da7'];
            var ns = 'http://www.w3.org/2000/svg';

            function element(name, attributes, text) {
                var node = document.createElementNS(ns, name);
                Object.keys(attributes || {}).forEach(function (key) {
                    node.setAttribute(key, attributes[key]);
                });
                if (text !== undefined) {
                    node.textContent = text;
                }
                return node;
            }

            function formatAmount(value) {
                return Number(value).toLocaleString(undefined, { maximumFractionDigits: 0 });
            }

            function chartScaffold(svg, maximum) {
                var left = 72;
                var right = 18;
                var top = 18;
                var bottom = 54;
                var width = 900 - left - right;
                var height = 340 - top - bottom;
                var scaleMax = maximum > 0 ? maximum : 1;
                for (var index = 0; index <= 4; index++) {
                    var y = top + (height * index / 4);
                    svg.appendChild(element('line', { x1: left, y1: y, x2: 900 - right, y2: y, 'class': 'grid-line' }));
                    svg.appendChild(element('text', { x: left - 10, y: y + 4, 'text-anchor': 'end' }, formatAmount(scaleMax * (4 - index) / 4)));
                }
                svg.appendChild(element('line', { x1: left, y1: top + height, x2: 900 - right, y2: top + height, 'class': 'axis-line' }));
                return { left: left, top: top, width: width, height: height, max: scaleMax, bottom: top + height };
            }

            function renderTrendChart(id, asColumns) {
                var svg = document.getElementById(id);
                var entries = data.timeline;
                if (!svg || !entries.length) {
                    return;
                }
                    var max = data.series.reduce(function (maximum, series) {
                        return series.values.reduce(function (seriesMaximum, value) {
                            return Math.max(seriesMaximum, Number(value));
                        }, maximum);
                    }, 0);
                    var chart = chartScaffold(svg, max);
                    var step = entries.length > 1 ? chart.width / (entries.length - 1) : 0;
                    var columnSlot = chart.width / entries.length;
                    data.series.forEach(function (series, seriesIndex) {
                        var seriesColor = colors[seriesIndex % colors.length];
                        var points = entries.map(function (entry, index) {
                            var value = Number(series.values[index]);
                            return {
                                x: asColumns
                                    ? chart.left + columnSlot * (index + .5) +
                                        (seriesIndex - (data.series.length - 1) / 2) *
                                        Math.min(42, columnSlot * .68) / data.series.length
                                    : chart.left + (entries.length > 1 ? step * index : chart.width / 2),
                                y: chart.bottom - (value / chart.max * chart.height),
                                value: value,
                                entry: entry
                            };
                        });

                        if (asColumns) {
                            var groupWidth = Math.min(42, columnSlot * .68);
                            var barWidth = groupWidth / data.series.length;
                            points.forEach(function (point) {
                                var bar = element('rect', {
                                    x: point.x - barWidth / 2,
                                    y: point.y,
                                    width: Math.max(1, barWidth - 1),
                                    height: Math.max(0, chart.bottom - point.y),
                                    fill: seriesColor,
                                    rx: 2
                                });
                                bar.appendChild(element('title', {}, series.label + ' - ' + point.entry.label + ': ' + formatAmount(point.value)));
                                svg.appendChild(bar);
                            });
                        } else {
                            var path = '';
                            points.forEach(function (point, index) {
                                if (index === 0) {
                                    path = 'M ' + point.x + ' ' + point.y;
                                } else {
                                    var previous = points[index - 1];
                                    var control = (point.x - previous.x) / 3;
                                    path += ' C ' + (previous.x + control) + ' ' + previous.y + ', ' +
                                        (point.x - control) + ' ' + point.y + ', ' + point.x + ' ' + point.y;
                                }
                            });
                            if (points.length === 1) {
                                path += ' L ' + points[0].x + ' ' + points[0].y;
                            }
                            svg.appendChild(element('path', {
                                d: path,
                                'class': 'income-line',
                                stroke: seriesColor
                            }));
                            points.forEach(function (point) {
                                var circle = element('circle', {
                                    cx: point.x,
                                    cy: point.y,
                                    r: 3.5,
                                    'class': 'income-point',
                                    stroke: seriesColor
                                });
                                circle.appendChild(element('title', {}, series.label + ' - ' + point.entry.label + ': ' + formatAmount(point.value)));
                                svg.appendChild(circle);
                            });
                        }
                    });

                    var labelStep = Math.max(1, Math.ceil(entries.length / 9));
                    entries.forEach(function (entry, index) {
                        if (index % labelStep === 0 || index === entries.length - 1) {
                            svg.appendChild(element('text', {
                                x: asColumns
                                    ? chart.left + columnSlot * (index + .5)
                                    : chart.left + (entries.length > 1 ? step * index : chart.width / 2),
                                y: chart.bottom + 22,
                                'text-anchor': 'middle'
                            }, entry.label));
                        }
                    });
                }

                function renderSeriesLegend(id) {
                    var legend = document.getElementById(id);
                    if (!legend) {
                        return;
                    }
                    data.series.forEach(function (series, index) {
                        var legendItem = document.createElement('li');
                        var swatch = document.createElement('span');
                        swatch.className = 'income-legend-swatch';
                        swatch.style.backgroundColor = colors[index % colors.length];
                        legendItem.appendChild(swatch);
                        legendItem.appendChild(document.createTextNode(series.label));
                        legend.appendChild(legendItem);
                    });
                }

            function renderPieChart() {
                var svg = document.getElementById('income-pie-chart');
                var legend = document.getElementById('income-pie-legend');
                if (!svg || !data.branches.length) {
                    return;
                }
                var total = data.branches.reduce(function (sum, item) {
                    return sum + Number(item.income);
                }, 0);
                if (total <= 0) {
                    svg.appendChild(element('text', { x: 210, y: 170, 'text-anchor': 'middle' }, 'No income to chart'));
                    return;
                }
                var centerX = 210;
                var centerY = 170;
                var radius = 130;
                var angle = -Math.PI / 2;
                data.branches.forEach(function (item, index) {
                    var slice = Number(item.income) / total * Math.PI * 2;
                    var endAngle = angle + slice;
                    var startX = centerX + radius * Math.cos(angle);
                    var startY = centerY + radius * Math.sin(angle);
                    var endX = centerX + radius * Math.cos(endAngle);
                    var endY = centerY + radius * Math.sin(endAngle);
                    var path = 'M ' + centerX + ' ' + centerY + ' L ' + startX + ' ' + startY +
                        ' A ' + radius + ' ' + radius + ' 0 ' + (slice > Math.PI ? 1 : 0) +
                        ' 1 ' + endX + ' ' + endY + ' Z';
                    var sliceNode = element('path', { d: path, fill: colors[index % colors.length], stroke: '#fff', 'stroke-width': 2 });
                    sliceNode.appendChild(element('title', {}, item.label + ': ' + formatAmount(item.income)));
                    svg.appendChild(sliceNode);

                    var legendItem = document.createElement('li');
                    var swatch = document.createElement('span');
                    swatch.className = 'income-legend-swatch';
                    swatch.style.backgroundColor = colors[index % colors.length];
                    legendItem.appendChild(swatch);
                    legendItem.appendChild(document.createTextNode(item.label + ' - ' + formatAmount(item.income)));
                    legend.appendChild(legendItem);
                    angle = endAngle;
                });
            }

            document.querySelectorAll('.select-all').forEach(function (button) {
                button.addEventListener('click', function () {
                    var select = document.getElementById(button.getAttribute('data-target'));
                    Array.prototype.forEach.call(select.options, function (option) {
                        option.selected = true;
                    });
                });
            });

            renderTrendChart('income-line-chart', false);
            renderTrendChart('income-column-chart', true);
            renderSeriesLegend('income-line-legend');
            renderSeriesLegend('income-column-legend');
            renderPieChart();
        }());
    </script>
</body>
</html>
