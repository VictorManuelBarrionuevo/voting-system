(function ($, Drupal) {
  Drupal.behaviors.showVoteWrapper = {
    attach: function (context, settings) {
      $('input[name^="answers_"]', context).on('change', function () {
        var answerId = $(this).attr('id');
        var questionId = answerId.split('-')[2];
        var voteWrapperSelector = '#edit-vote-' + questionId + '--wrapper';
        $(voteWrapperSelector, context).show();
      });
    }
  };
})(jQuery, Drupal);
