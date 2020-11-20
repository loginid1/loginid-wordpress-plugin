const path = require("path");
const webpack = require("webpack");
const CopyPlugin = require("copy-webpack-plugin");

module.exports = {
  entry: "./src/main.js", // Entry File
  output: {
    path: path.resolve(__dirname, "includes"), //Output Directory
    filename: "main.js", //Output file
  },
  plugins: [
    new CopyPlugin({
      patterns: [
        { from: "src/main.css", to: "main.css" },
      ],
    }),
  ],
};