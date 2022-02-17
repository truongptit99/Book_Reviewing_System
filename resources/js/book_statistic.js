import I18n from './vendor/I18n';
window.I18n = I18n;
let translator = new I18n;

window.onload = function () {
    var likeChart = $('#like-chart'),
        reviewChart = $('#review-chart'),
        commentChart = $('#comment-chart'),
        likes_statistic = JSON.parse($('#likes-statistic').val()),
        reviews_statistic = JSON.parse($('#reviews-statistic').val()),
        comments_statistic = JSON.parse($('#comments-statistic').val()),
        likes = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        reviews = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        comments = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

    for (var i = 0; i < likes_statistic.length; i++) {
        likes[likes_statistic[i]['month'] - 1] = likes_statistic[i]['total_like'];
    }
    for (var i = 0; i < reviews_statistic.length; i++) {
        reviews[reviews_statistic[i]['month'] - 1] = reviews_statistic[i]['total_review'];
    }
    for (var i = 0; i < comments_statistic.length; i++) {
        comments[comments_statistic[i]['month'] - 1] = comments_statistic[i]['total_cmt'];
    }

    var labels = [
        translator.trans('messages.jan'),
        translator.trans('messages.feb'),
        translator.trans('messages.mar'),
        translator.trans('messages.apr'),
        translator.trans('messages.may'),
        translator.trans('messages.jun'),
        translator.trans('messages.jul'),
        translator.trans('messages.aug'),
        translator.trans('messages.sep'),
        translator.trans('messages.oct'),
        translator.trans('messages.nov'),
        translator.trans('messages.dec'),
    ];

    var myChart1 = new Chart(likeChart, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Like',
                    data: likes,
                    backgroundColor: '#00d4ff'
                },
            ]
        },
        options: {
            scales: {
              y: {
                beginAtZero: true
              }
            }
        },
    });
    var myChart2 = new Chart(reviewChart, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Review',
                    data: reviews,
                    backgroundColor: '#00d4ff'
                },
            ]
        },
        options: {
            scales: {
              y: {
                beginAtZero: true
              }
            }
        },
    });
    var myChart3 = new Chart(commentChart, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Comment',
                    data: comments,
                    backgroundColor: '#00d4ff'
                },
            ]
        },
        options: {
            scales: {
              y: {
                beginAtZero: true
              }
            }
        },
    });
};
