Journo
======

    Journo = module.exports = {}

Journo is a blogging program, with a few basic goals. To wit:

* Write in Markdown.

* Publish to flat files.

* Publish via Rsync.

* Maintain a manifest file (what's published and what isn't, pub dates).

* Retina ready.

* Syntax highlight code.

* Publish a feed.

* Quickly bootstrap a new blog.

* Preview via a local server.

* Work without JavaScript, but default to a fluid JavaScript-enabled UI.

You can install and use the `journo` command via npm: `sudo npm install -g journo`

... now, let's go through those features one at a time:


Getting Started
---------------

1. Create a folder for your blog, and `cd` into it.

2. Type `journo init` to bootstrap a new empty blog.

3. Edit the `config.json`, `layout.html`, and `posts/index.md` files to suit.

4. Type `journo` to start the preview server, and have at it.


Write in Markdown
-----------------

We'll use the excellent **marked** module to compile Markdown into HTML, and
Underscore for many of its goodies later on. Up top, create a namespace for
shared values needed by more than one function.

    marked = require 'marked'
    _ = require 'underscore'
    shared = {}

To render a post, we take its raw `source`, treat it as both an Underscore
template (for HTML generation) and as Markdown (for formatting), and insert it
into the layout as `content`.

    Journo.render = (post, source) ->
      catchErrors ->
        do loadLayout
        source or= fs.readFileSync postPath post
        variables = renderVariables post
        markdown  = _.template(source.toString()) variables
        title     = detectTitle markdown
        content   = marked.parser marked.lexer markdown
        shared.layout _.extend variables, {title, content}

A Journo site has a layout file, stored in `layout.html`, which is used
to wrap every page.

    loadLayout = (force) ->
      return layout if not force and layout = shared.layout
      shared.layout = _.template(fs.readFileSync('layout.html').toString())

Determine the appropriate command to "open" a url in the browser for the
current platform.

    opener = switch process.platform
      when 'darwin' then 'open'
      when 'win32' then 'start'
      else 'xdg-open'


Publish to Flat Files
---------------------

A blog is a folder on your hard drive. Within the blog, you have a `posts`
folder for blog posts, a `public` folder for static content, a `layout.html`
file for the layout which wraps every page, and a `journo.json` file for
configuration. During a `build`, a static version of the site is rendered
into the `site` folder, by **rsync**ing over all static files, rendering and
writing every post, and creating an RSS feed.

    fs = require 'fs'
    path = require 'path'
    {spawn, exec} = require 'child_process'

    Journo.build = ->
      do loadManifest
      fs.mkdirSync('site') unless fs.existsSync('site')

      exec "rsync -vur --delete public/ site", (err, stdout, stderr) ->
        throw err if err

        for post in folderContents('posts')
          html = Journo.render post
          file = htmlPath post
          fs.mkdirSync path.dirname(file) unless fs.existsSync path.dirname(file)
          fs.writeFileSync file, html

        fs.writeFileSync "site/feed.rss", Journo.feed()

The `config.json` configuration file is where you keep the configuration
details of your blog, and how to connect to the server you'd like to publish
it on. The valid settings are: `title`, `description`, `author` (for RSS), `url
`, `publish` (the `user@host:path` location to **rsync** to), and `publishPort`
(if your server doesn't listen to SSH on the usual one).

An example `config.json` will be bootstrapped for you when you initialize a blog,
so you don't need to remember any of that.

    loadConfig = ->
      return if shared.config
      try
        shared.config = JSON.parse fs.readFileSync 'config.json'
      catch err
        fatal "Unable to read config.json"
      shared.siteUrl = shared.config.url.replace(/\/$/, '')


Publish via rsync
-----------------

Publishing is nice and rudimentary. We build out an entirely static version of
the site and **rysnc** it up to the server.

    Journo.publish = ->
      do Journo.build
      rsync 'site/images/', path.join(shared.config.publish, 'images/'), ->
        rsync 'site/', shared.config.publish

A helper function for **rsync**ing, with logging, and the ability to wait for
the rsync to continue before proceeding. This is useful for ensuring that our
any new photos have finished uploading (very slowly) before the update to the feed
is syndicated out.

    rsync = (from, to, callback) ->
      port = "ssh -p #{shared.config.publishPort or 22}"
      child = spawn "rsync", ['-vurz', '--delete', '-e', port, from, to]
      child.stdout.on 'data', (out) -> console.log out.toString()
      child.stderr.on 'data', (err) -> console.error err.toString()
      child.on 'exit', callback if callback


Maintain a Manifest File
------------------------

The "manifest" is where Journo keeps track of metadata -- the title, description,
publications date and last modified time of each post. Everything you need to
render out an RSS feed ... and everything you need to know if a post has been
updated or removed.

    manifestPath = 'journo-manifest.json'

    loadManifest = ->
      do loadConfig

      shared.manifest = if fs.existsSync manifestPath
        JSON.parse fs.readFileSync manifestPath
      else
        {}

      do updateManifest
      fs.writeFileSync manifestPath, JSON.stringify shared.manifest

We update the manifest by looping through every post and every entry in the
existing manifest, looking for differences in `mtime`, and recording those
along with the title and description of each post.

    updateManifest = ->
      manifest = shared.manifest
      posts = folderContents 'posts'

      delete manifest[post] for post of manifest when post not in posts

      for post in posts
        stat = fs.statSync postPath post
        entry = manifest[post]
        if not entry or entry.mtime isnt stat.mtime
          entry or= {pubtime: stat.ctime}
          entry.mtime = stat.mtime
          content = fs.readFileSync(postPath post).toString()
          entry.title = detectTitle content
          entry.description = detectDescription content, post
          manifest[post] = entry

      yes


Retina Ready
------------

In the future, it may make sense for Journo to have some sort of built-in
facility for automatically downsizing photos from retina to regular sizes ...
But for now, this bit is up to you.


Syntax Highlight Code
---------------------

We syntax-highlight blocks of code with the nifty **highlight** package that
includes heuristics for auto-language detection, so you don't have to specify
what you're coding in.

    highlight = require 'highlight.js'

    marked.setOptions
      highlight: (code, lang) ->
        if highlight.LANGUAGES[lang]?
          highlight.highlight(lang, code, true).value
        else
          highlight.highlightAuto(code).value

Publish a Feed
--------------

We'll use the **rss** module to build a simple feed of recent posts. Start with
the basic `author`, blog `title`, `description` and `url` configured in the
`config.json`. Then, each post's `title` is the first header present in the
post, the `description` is the first paragraph, and the date is the date you
first created the post file.

    Journo.feed = ->
      RSS = require 'rss'
      do loadConfig
      config = shared.config

      feed = new RSS
        title: config.title
        description: config.description
        feed_url: "#{shared.siteUrl}/rss.xml"
        site_url: shared.siteUrl
        author: config.author

      for post in sortedPosts().reverse()[0...20]
        entry = shared.manifest[post]
        feed.item
          title: entry.title
          description: entry.description
          url: postUrl post
          date: entry.pubtime

      feed.xml()


Quickly Bootstrap a New Blog
----------------------------

We **init** a new blog into the current directory by copying over the contents
of a basic `bootstrap` folder.

    Journo.init = ->
      here = fs.realpathSync '.'
      if fs.existsSync 'posts'
        fatal "A blog already exists in #{here}"
      bootstrap = path.join(__dirname, 'bootstrap/*')
      exec "rsync -vur --delete #{bootstrap} .", (err, stdout, stderr) ->
        throw err if err
        console.log "Initialized new blog in #{here}"


Preview via a Local Server
--------------------------

Instead of constantly rebuilding a purely static version of the site, Journo
provides a preview server (which you can start by just typing `journo` from
within your blog).

    Journo.preview = ->
      http = require 'http'
      mime = require 'mime'
      url = require 'url'
      util = require 'util'
      do loadManifest

      server = http.createServer (req, res) ->
        rawPath = url.parse(req.url).pathname.replace(/(^\/|\/$)/g, '') or 'index'

If the request is for a preview of the RSS feed...

        if rawPath is 'feed.rss'
          res.writeHead 200, 'Content-Type': mime.lookup('.rss')
          res.end Journo.feed()

If the request is for a static file that exists in our `public` directory...

        else
          publicPath = "public/" + rawPath
          fs.exists publicPath, (exists) ->
            if exists
              res.writeHead 200, 'Content-Type': mime.lookup(publicPath)
              fs.createReadStream(publicPath).pipe res

If the request is for the slug of a valid post, we reload the layout, and
render it...

            else
              post = "posts/#{rawPath}.md"
              fs.exists post, (exists) ->
                if exists
                  loadLayout true
                  fs.readFile post, (err, content) ->
                    res.writeHead 200, 'Content-Type': 'text/html'
                    res.end Journo.render post, content

Anything else is a 404.

                else
                  res.writeHead 404
                  res.end '404 Not Found'

      server.listen 1234
      console.log "Journo is previewing at http://localhost:1234"
      exec "#{opener} http://localhost:1234"


Work Without JavaScript, But Default to a Fluid JavaScript-Enabled UI
---------------------------------------------------------------------

The best way to handle this bit seems to be entirely on the client-side. For
example, when rendering a JavaScript slideshow of photographs, instead of
having the server spit out the slideshow code, simply have the blog detect
the list of images during page load and move them into a slideshow right then
and there -- using `alt` attributes for captions, for example.

Since the blog is public, it's nice if search engines can see all of the pieces
as well as readers.


Finally, Putting it all Together. Run Journo From the Terminal
--------------------------------------------------------------

We'll do the simplest possible command-line interface. If a public function
exists on the `Journo` object, you can run it. *Note that this lets you do
silly things, like* `journo toString` *but no big deal.*

    Journo.run = ->
      command = process.argv[2] or 'preview'
      return do Journo[command] if Journo[command]
      console.error "Journo doesn't know how to '#{command}'"

Let's also provide a help page that lists the available commands.

    Journo.help = Journo['--help'] = ->
      console.log """
        Usage: journo [command]

        If called without a command, `journo` will preview your blog.

        init      start a new blog in the current folder
        build     build a static version of the blog into 'site'
        preview   live preview the blog via a local server
        publish   publish the blog to your remote server
      """

And we might as well do the version number, for completeness' sake.

    Journo.version = Journo['--version'] = ->
      console.log "Journo 0.0.1"


Miscellaneous Bits and Utilities
--------------------------------

Little utility functions that are useful up above.

The file path to the source of a given `post`.

    postPath = (post) -> "posts/#{post}"

The server-side path to the HTML for a given `post`.

    htmlPath = (post) ->
      name = postName post
      if name is 'index'
        'site/index.html'
      else
        "site/#{name}/index.html"

The name (or slug) of a post, taken from the filename.

    postName = (post) -> path.basename post, '.md'

The full, absolute URL for a published post.

    postUrl = (post) -> "#{shared.siteUrl}/#{postName(post)}/"

Starting with the string contents of a post, detect the title --
the first heading.

    detectTitle = (content) ->
      _.find(marked.lexer(content), (token) -> token.type is 'heading')?.text

Starting with the string contents of a post, detect the description --
the first paragraph.

    detectDescription = (content, post) ->
      desc = _.find(marked.lexer(content), (token) -> token.type is 'paragraph')?.text
      marked.parser marked.lexer _.template("#{desc}...")(renderVariables(post))

Helper function to read in the contents of a folder, ignoring hidden files
and directories.

    folderContents = (folder) ->
      fs.readdirSync(folder).filter (f) -> f.charAt(0) isnt '.'

Return the list of posts currently in the manifest, sorted by their date of
publication.

    sortedPosts = ->
      _.sortBy _.without(_.keys(shared.manifest), 'index.md'), (post) ->
        shared.manifest[post].pubtime

The shared variables we want to allow our templates (both posts, and layout)
to use in their evaluations. In the future, it would be nice to determine
exactly what best belongs here, and provide an easier way for the blog author
to add functions to it.

    renderVariables = (post) ->
      {
        _
        fs
        path
        mapLink
        postName
        folderContents
        posts: sortedPosts()
        post: path.basename(post)
        manifest: shared.manifest
      }

Quick function which creates a link to a Google Map search for the name of the
place.

    mapLink = (place, additional = '', zoom = 15) ->
      query = encodeURIComponent("#{place}, #{additional}")
      "<a href=\"https://maps.google.com/maps?q=#{query}&t=h&z=#{zoom}\">#{place}</a>"

Convenience function for catching errors (keeping the preview server from
crashing while testing code), and printing them out.

    catchErrors = (func) ->
      try do func
      catch err
        console.error err.stack
        "<pre>#{err.stack}</pre>"

Finally, for errors that you want the app to die on -- things that should break
the site build.

    fatal = (message) ->
      console.error message
      process.exit 1



