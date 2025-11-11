Swift Removals - Static Blog Package

Files included (ready to upload to your cPanel public_html):

/blog/
  index.html        -> blog list (client-side)
  post.html         -> single post viewer (client-side + JSON-LD)
  posts.json        -> posts data (3 sample posts included)
  styles.css        -> blog styles
  rss.xml           -> RSS template (auto-generated for samples)
/blog/<slug>/index.html -> pre-rendered static page for each post (best for SEO & social preview)
/images/blog/       -> image folder placeholder (we used Unsplash URLs for cover images)
README.txt

How to deploy:
1. Unzip blog_package.zip and upload the 'blog' folder into your site's public_html directory so that:
   - https://swiftremovals.co.za/blog/ shows the blog index
   - https://swiftremovals.co.za/blog/top-10-packing-tips-for-a-stress-free-move/ shows the static post page
2. Replace the cover_image values in blog/posts.json if you want to use site-hosted images.
3. Upload a logo at /images/logo.png for JSON-LD publisher logo (optional but recommended).
4. Add links from your homepage to /blog/ and add entries in your sitemap.xml.
5. Submit sitemap or new URLs to Google Search Console for faster indexing.

Notes:
- The package includes pre-rendered pages in /blog/<slug>/index.html (these contain full meta tags and OG tags), which is best for SEO & social sharing.
- If you'd rather have server-side generation, ask me and I can provide a Node script.