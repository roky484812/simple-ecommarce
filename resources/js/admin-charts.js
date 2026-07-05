import ApexCharts from 'apexcharts';

/**
 * Alpine.js component rendering a donut chart of order counts by status.
 * The tooltip on hover shows the order count, quantity sold, and revenue
 * amount for that status.
 *
 * Usage: <div x-data="ordersByStatusChart(@js($labels), @js($counts), @js($quantities), @js($amounts))">
 */
export function ordersByStatusChart(labels, counts, quantities, amounts) {
    return {
        chart: null,

        init() {
            const hasData = counts.some((value) => value > 0);

            this.chart = new ApexCharts(this.$el, {
                chart: {
                    type: 'donut',
                    height: 280,
                },
                labels,
                series: hasData ? counts : [],
                colors: ['#9ca3af', '#3b82f6', '#6366f1', '#22c55e', '#ef4444'],
                legend: {
                    position: 'bottom',
                },
                dataLabels: {
                    enabled: true,
                },
                tooltip: {
                    custom: ({ seriesIndex, w }) => {
                        const label = w.globals.labels[seriesIndex];
                        const count = counts[seriesIndex];
                        const qty = quantities[seriesIndex];
                        const amount = Number(amounts[seriesIndex]).toLocaleString();

                        return `
                            <div class="px-3 py-2 text-sm bg-white text-gray-900 rounded shadow-lg border border-gray-200">
                                <div class="font-semibold mb-1">${label}</div>
                                <div>Orders: ${count}</div>
                                <div>Quantity: ${qty}</div>
                                <div>Amount: ৳${amount}</div>
                            </div>
                        `;
                    },
                },
                noData: {
                    text: 'No orders yet',
                },
            });

            this.chart.render();
        },

        destroy() {
            this.chart?.destroy();
        },
    };
}

/**
 * Alpine.js component rendering a horizontal bar chart of units sold per product.
 * The tooltip on hover shows the quantity sold and revenue amount for that product.
 *
 * Usage: <div x-data="topProductsChart(@js($labels), @js($series), @js($amounts))">
 */
export function topProductsChart(labels, series, amounts) {
    return {
        chart: null,

        init() {
            this.chart = new ApexCharts(this.$el, {
                chart: {
                    type: 'bar',
                    height: 280,
                    toolbar: {
                        show: false,
                    },
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                    },
                },
                dataLabels: {
                    enabled: true,
                },
                xaxis: {
                    categories: labels,
                    allowDecimals: false,
                },
                colors: ['#3b82f6'],
                series: [{
                    name: 'Units sold',
                    data: series,
                }],
                tooltip: {
                    custom: ({ dataPointIndex }) => {
                        const label = labels[dataPointIndex];
                        const qty = series[dataPointIndex];
                        const amount = Number(amounts[dataPointIndex]).toLocaleString();

                        return `
                            <div class="px-3 py-2 text-sm bg-white text-gray-900 rounded shadow-lg border border-gray-200">
                                <div class="font-semibold mb-1">${label}</div>
                                <div>Quantity sold: ${qty}</div>
                                <div>Amount: ৳${amount}</div>
                            </div>
                        `;
                    },
                },
                noData: {
                    text: 'No sales yet',
                },
            });

            this.chart.render();
        },

        destroy() {
            this.chart?.destroy();
        },
    };
}

/**
 * Alpine.js component rendering a multi-series chart of daily order count,
 * quantity sold, and revenue amount, showing the trend rising and falling
 * over the last 30 days.
 *
 * Usage: <div x-data="orderTrendChart(@js($dates), @js($counts), @js($quantities), @js($amounts))">
 */
export function orderTrendChart(dates, counts, quantities, amounts) {
    return {
        chart: null,

        init() {
            this.chart = new ApexCharts(this.$el, {
                chart: {
                    type: 'line',
                    height: 320,
                    toolbar: {
                        show: false,
                    },
                    zoom: {
                        enabled: false,
                    },
                },
                stroke: {
                    curve: 'smooth',
                    width: [2, 2, 3],
                },
                dataLabels: {
                    enabled: false,
                },
                xaxis: {
                    categories: dates,
                    type: 'datetime',
                },
                yaxis: [
                    {
                        title: {
                            text: 'Orders / Qty',
                        },
                        allowDecimals: false,
                        min: 0,
                    },
                    {
                        show: false,
                        allowDecimals: false,
                        min: 0,
                    },
                    {
                        opposite: true,
                        title: {
                            text: 'Amount (৳)',
                        },
                        min: 0,
                        labels: {
                            formatter: (value) => `৳${Math.round(value).toLocaleString()}`,
                        },
                    },
                ],
                colors: ['#6366f1', '#22c55e', '#f59e0b'],
                series: [
                    {
                        name: 'Orders',
                        type: 'line',
                        data: counts,
                    },
                    {
                        name: 'Quantity Sold',
                        type: 'line',
                        data: quantities,
                    },
                    {
                        name: 'Amount',
                        type: 'area',
                        data: amounts,
                    },
                ],
                tooltip: {
                    y: {
                        formatter: (value, { seriesIndex }) => (seriesIndex === 2 ? `৳${Number(value).toLocaleString()}` : value),
                    },
                },
                noData: {
                    text: 'No orders yet',
                },
            });

            this.chart.render();
        },

        destroy() {
            this.chart?.destroy();
        },
    };
}
