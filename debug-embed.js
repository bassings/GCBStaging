async function debug() {
  const baseURL = 'http://localhost:8881';

  // Reset database
  await fetch(`${baseURL}/wp-json/gcb-testing/v1/reset`, {
    method: 'DELETE',
    headers: { 'GCB-Test-Key': 'test-secret-key-local' }
  });

  // Create post
  const response = await fetch(`${baseURL}/wp-json/gcb-testing/v1/create-post`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'GCB-Test-Key': 'test-secret-key-local'
    },
    body: JSON.stringify({
      title: 'Debug Embed Test',
      content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
      status: 'publish'
    })
  });

  const post = await response.json();
  console.log('Post created:', post.link);
  console.log('\nPost content (raw):', post.content.raw);
  console.log('\nPost content (rendered):', post.content.rendered);
  console.log('\nMeta _gcb_content_format:', post.meta._gcb_content_format);
  console.log('\nMeta _gcb_video_id:', post.meta._gcb_video_id);

  // Fetch the actual page HTML
  const pageResponse = await fetch(post.link);
  const html = await pageResponse.text();

  // Check for embed blocks
  const hasEmbedBlock = html.includes('wp-block-embed');
  const hasYoutubeEmbed = html.includes('wp-block-embed-youtube');
  const hasIframe = html.includes('<iframe');
  const hasYoutubeIframe = html.includes('youtube.com/embed');

  console.log('\n--- Page Analysis ---');
  console.log('Has wp-block-embed class:', hasEmbedBlock);
  console.log('Has wp-block-embed-youtube class:', hasYoutubeEmbed);
  console.log('Has <iframe> tag:', hasIframe);
  console.log('Has youtube.com/embed in iframe:', hasYoutubeIframe);

  // Extract embed block if present
  const embedMatch = html.match(/<figure class="wp-block-embed[^"]*">[\s\S]*?<\/figure>/);
  if (embedMatch) {
    console.log('\n--- Embed Block HTML ---');
    console.log(embedMatch[0]);
  } else {
    console.log('\nNo embed block found in HTML');
  }

  // Check for YouTube URL in content
  if (html.includes('youtube.com')) {
    console.log('\n--- Found YouTube reference ---');
    const youtubeMatches = html.match(/youtube\.com[^\s<"]*/g);
    console.log(youtubeMatches);
  }
}

debug().catch(console.error);
