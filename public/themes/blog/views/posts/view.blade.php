<div class="blog-post">
  <h2 class="blog-post-title">
    {{ $post['post']->name }}
  </h2>

  <p class="blog-post-meta">
    {{ Core::getDateLong($post['post']->created_at) }} by
    <a href="{{ route('users.profile.view', [ 'username' => $post['post']->user->username ]) }}">
      {{ $post['post']->user->username }}
    </a>
  </p>

  {!! $post['post']->getFieldValue($post['post_data'], 'blog-content') !!}
</div><!-- /.blog-post -->

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