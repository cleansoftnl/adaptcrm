<div class="ui grid margin-bottom-10">
  <div class="ui divided items">
    <div class="item">
      <div class="image">
        <?php $image = $post['post']->getFieldValue($post['post_data'], 'image') ?>

        @if(!empty($image))
          <img src="{{ $image }}">
        @endif
      </div>
      <div class="content">
        <h1 class="header">{{ $post['post']->name }}</h1>
        <div class="meta">
          <span class="cinema"><small>Posted: {{ Core::getDateLong($post['post']->created_at) }}</small></span>
        </div>
        <div class="description text-color-black">
          {!! $post['post']->getFieldValue($post['post_data'], 'body') !!}
        </div>
        <div class="extra">
          @if(!empty($post['tags']))
            @foreach($post['tags'] as $tag)
              <div class="ui label"><a href="{{ route('tags.view', [ 'slug' => $tag->slug ]) }}">{{ $tag->name }}</a>
              </div>
            @endforeach
          @endif
        </div>
      </div>
    </div>
  </div>
</div>


<div id="disqus_thread"></div>
<script>

  /**
   *  RECOMMENDED CONFIGURATION VARIABLES: EDIT AND UNCOMMENT THE SECTION BELOW TO INSERT DYNAMIC VALUES FROM YOUR PLATFORM OR CMS.
   *  LEARN WHY DEFINING THESE VARIABLES IS IMPORTANT: https://disqus.com/admin/universalcode/#configuration-variables*/

  var disqus_config = function () {
    this.page.url = '{{ Request::url() }}';  // Replace PAGE_URL with your page's canonical URL variable
    this.page.identifier = '{{ env('APP_KEY') }}_{{ $post['post']->id }}'; // Replace PAGE_IDENTIFIER with your page's unique identifier variable
  };

  (function () { // DON'T EDIT BELOW THIS LINE
    var d = document, s = d.createElement('script');
    s.src = '//adaptcms.disqus.com/embed.js';
    s.setAttribute('data-timestamp', +new Date());
    (d.head || d.body).appendChild(s);
  })();
</script>
<noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript">comments powered by
    Disqus.</a></noscript>