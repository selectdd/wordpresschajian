jQuery(document).ready(function($) {
    $('#anti-counterfeit-form').submit(function(e) {
        e.preventDefault();
        var code = $('#anti-counterfeit-code').val();

        $.ajax({
            type: 'POST',
            url: antiCounterfeitAjax.ajaxurl,
            data: {
                action: 'anti_counterfeit_ajax',
                code: code,
            },
            success: function(response) {
                try {
                    response = JSON.parse(response);
                    if (response.success) {
                        $('#anti-counterfeit-result').html('<p>查询成功：' + response.message + '</p>');
                    } else {
                        $('#anti-counterfeit-result').html('<p>查询失败：' + response.message + '</p>');
                    }
                } catch (error) {
                    $('#anti-counterfeit-result').html('<p>响应格式不正确</p>');
                }
                $('#anti-counterfeit-result').show(); 
            },
            error: function() {
                $('#anti-counterfeit-result').html('<p>查询时发生错误。</p>');
                $('#anti-counterfeit-result').show(); 
            }
        });
    });
});
