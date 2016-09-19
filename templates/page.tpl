<section id="gallery">
    {foreach $gallery->GetImages() as $image}
        <img src="{uploads_url}/images/{$image}" alt="{$image|basename|htmlentities}">
    {/foreach}
</section>