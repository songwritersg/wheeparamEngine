<textarea name="<?=$name?>" id="<?=$id?>"><?=$contents?></textarea>
<script>
$(function(){
    tinymce.init({
        selector : 'textarea#<?=$id?>',
        menubar:false,
        height : '<?=$height?>'
    });
});
</script>